import { Component, Inject, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule, DOCUMENT } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { CandidateService } from '../../services/candidate.service';
import { JobService, JobOffer } from '../../services/job.service';
import { NotificationService } from '../../services/notification.service';
import { CandidateSubmission } from '../../interfaces/candidate.interface';

@Component({
  selector: 'app-cv-submission',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSelectModule
  ],
  templateUrl: './cv-submission.html',
  styleUrl: './cv-submission.scss'
})
export class CvSubmission implements OnInit {
  candidateForm: FormGroup;
  isSubmitting = false;
  cvFile: File | null = null;
  lettreFile: File | null = null;

  // Gestion des offres d'emploi
  jobOffers: JobOffer[] = [];
  selectedJobOffer: JobOffer | null = null;
  loadingOffers = true;
  selectedJobId: number | null = null;

  constructor(
    private fb: FormBuilder,
    private candidateService: CandidateService,
    private jobService: JobService,
    private notificationService: NotificationService,
    private route: ActivatedRoute,
    private router: Router,
    @Inject(DOCUMENT) public document: Document
  ) {
    this.candidateForm = this.createForm();
  }

  ngOnInit() {
    this.loadJobOffers();
    this.checkForPreselectedJob();
  }

  private checkForPreselectedJob() {
    // Vérifier si un jobId est passé en paramètre de requête
    this.route.queryParams.subscribe(params => {
      if (params['jobId']) {
        this.selectedJobId = parseInt(params['jobId']);
        console.log('Job ID détecté dans les paramètres:', this.selectedJobId);
        // Si les offres sont déjà chargées, mettre à jour immédiatement
        if (this.jobOffers.length > 0) {
          this.updateSelectedJobFromList();
        }
        // Sinon, loadSelectedJobDetails sera appelé après le chargement des offres
        this.loadSelectedJobDetails();
      }
    });

    // Vérifier aussi si une offre est passée via la navigation state
    const navigation = this.router.getCurrentNavigation();
    if (navigation?.extras?.state?.['jobOffer']) {
      const jobOffer = navigation.extras.state['jobOffer'];
      this.selectedJobOffer = jobOffer;
      this.selectedJobId = jobOffer.id;
      console.log('Offre détectée via navigation state:', jobOffer.title);
      // Mettre à jour le formulaire immédiatement
      this.candidateForm.patchValue({
        job_offer_id: jobOffer.id
      });
    }
  }

  private loadJobOffers() {
    this.loadingOffers = true;
    this.jobService.getJobOffers().subscribe({
      next: (response: any) => {
        if (response.success) {
          this.jobOffers = response.data;
          // Après avoir chargé les offres, vérifier si on doit pré-sélectionner une offre
          this.updateSelectedJobFromList();
        }
        this.loadingOffers = false;
      },
      error: (err: any) => {
        console.error('Erreur lors du chargement des offres:', err);
        this.notificationService.showError('Impossible de charger les offres d\'emploi');
        this.loadingOffers = false;
      }
    });
  }

  private loadSelectedJobDetails() {
    if (this.selectedJobId) {
      this.jobService.getJobOffer(this.selectedJobId).subscribe({
        next: (response: any) => {
          if (response.success) {
            this.selectedJobOffer = response.data;
            // Mettre à jour le formulaire avec l'offre chargée
            this.candidateForm.patchValue({
              job_offer_id: this.selectedJobOffer.id
            });
          }
        },
        error: (err: any) => {
          console.error('Erreur lors du chargement de l\'offre:', err);
        }
      });
    }
  }

  private updateSelectedJobFromList() {
    // Si on a un selectedJobId et que les offres sont chargées
    if (this.selectedJobId && this.jobOffers.length > 0) {
      // Trouver l'offre correspondante dans la liste
      const foundOffer = this.jobOffers.find(offer => offer.id === this.selectedJobId);
      if (foundOffer) {
        this.selectedJobOffer = foundOffer;
        // Mettre à jour le formulaire pour que la liste déroulante affiche la bonne valeur
        this.candidateForm.patchValue({
          job_offer_id: foundOffer.id
        });
        console.log('Offre pré-sélectionnée:', foundOffer.title);
      }
    }
  }

  private createForm(): FormGroup {
    return this.fb.group({
      nom: ['', [Validators.required, Validators.minLength(2)]],
      prenom: ['', [Validators.required, Validators.minLength(2)]],
      email: ['', [Validators.required, Validators.email]],
      telephone: ['', [Validators.required, Validators.pattern(/^[0-9+\-\s()]+$/)]],
      linkedin_url: ['', [Validators.pattern(/^https?:\/\/(www\.)?linkedin\.com\/.*/)]],
      job_offer_id: ['', [Validators.required]]
    });
  }

  onJobOfferChange(jobOfferId: number) {
    this.selectedJobId = jobOfferId;
    this.selectedJobOffer = this.jobOffers.find(offer => offer.id === jobOfferId) || null;
  }

  getExperienceLevelLabel(level: string): string {
    const labels: { [key: string]: string } = {
      'junior': 'Débutant',
      'intermediate': 'Intermédiaire',
      'senior': 'Senior',
      'expert': 'Expert'
    };
    return labels[level] || level;
  }

  getContractTypeLabel(type: string): string {
    const labels: { [key: string]: string } = {
      'CDI': 'CDI',
      'CDD': 'CDD',
      'Stage': 'Stage',
      'Freelance': 'Freelance',
      'Alternance': 'Alternance'
    };
    return labels[type] || type;
  }

  onCvFileSelected(event: any): void {
    const file = event.target.files[0];
    if (file && this.isValidPdfFile(file)) {
      this.cvFile = file;
    } else {
      this.notificationService.showError('Veuillez sélectionner un fichier PDF valide pour le CV');
      event.target.value = '';
    }
  }

  onLettreFileSelected(event: any): void {
    const file = event.target.files[0];
    if (file && this.isValidPdfFile(file)) {
      this.lettreFile = file;
    } else {
      this.notificationService.showError('Veuillez sélectionner un fichier PDF valide pour la lettre de motivation');
      event.target.value = '';
    }
  }

  private isValidPdfFile(file: File): boolean {
    const maxSize = 5 * 1024 * 1024; // 5MB
    return file.type === 'application/pdf' && file.size <= maxSize;
  }

  onSubmit(): void {
    console.log('=== DEBUT SOUMISSION ===');
    console.log('Form valid:', this.candidateForm.valid);
    console.log('Form value:', this.candidateForm.value);
    console.log('CV File:', this.cvFile);
    console.log('Lettre File:', this.lettreFile);
    console.log('Form errors:', this.candidateForm.errors);

    if (this.candidateForm.valid && this.cvFile && this.lettreFile) {
      this.isSubmitting = true;

      const candidateData: CandidateSubmission = {
        ...this.candidateForm.value,
        cv_file: this.cvFile,
        lettre_motivation_file: this.lettreFile
      };

      console.log('Candidate data to send:', candidateData);

      this.candidateService.submitCandidate(candidateData).subscribe({
        next: (response) => {
          console.log('=== RÉPONSE CANDIDATURE REÇUE ===');
          console.log('Response:', response);
          console.log('Response success:', response.success);
          console.log('Response message:', response.message);

          if (response.success) {
            this.notificationService.showSuccess('Votre candidature a été soumise avec succès !');
            this.resetForm();
          } else {
            this.notificationService.showError(response.message || 'Erreur lors de la soumission');
          }
          this.isSubmitting = false;
        },
        error: (error) => {
          console.error('=== ERREUR SOUMISSION ===');
          console.error('Error object:', error);
          console.error('Error message:', error.message);
          console.error('Error status:', error.status);
          this.notificationService.showError(error || 'Une erreur est survenue lors de la soumission');
          this.isSubmitting = false;
        }
      });
    } else {
      this.markFormGroupTouched();
      if (!this.cvFile) {
        this.notificationService.showError('Veuillez sélectionner votre CV');
      }
      if (!this.lettreFile) {
        this.notificationService.showError('Veuillez sélectionner votre lettre de motivation');
      }
    }
  }

  private markFormGroupTouched(): void {
    Object.keys(this.candidateForm.controls).forEach(key => {
      this.candidateForm.get(key)?.markAsTouched();
    });
  }

  resetForm(): void {
    this.candidateForm.reset();
    this.cvFile = null;
    this.lettreFile = null;
  }

  getErrorMessage(fieldName: string): string {
    const field = this.candidateForm.get(fieldName);
    if (field?.hasError('required')) {
      return `${fieldName} est requis`;
    }
    if (field?.hasError('email')) {
      return 'Email invalide';
    }
    if (field?.hasError('minlength')) {
      return `${fieldName} doit contenir au moins ${field.errors?.['minlength']?.requiredLength} caractères`;
    }
    if (field?.hasError('pattern')) {
      if (fieldName === 'telephone') {
        return 'Numéro de téléphone invalide';
      }
      if (fieldName === 'linkedin_url') {
        return 'URL LinkedIn invalide';
      }
    }
    return '';
  }
}
