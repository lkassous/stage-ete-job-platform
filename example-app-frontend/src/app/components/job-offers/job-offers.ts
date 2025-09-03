import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { JobService, JobOffer } from '../../services/job.service';

@Component({
  selector: 'app-job-offers',
  imports: [CommonModule, FormsModule],
  templateUrl: './job-offers.html',
  styleUrl: './job-offers.scss'
})
export class JobOffersComponent implements OnInit {
  jobOffers: any[] = [];
  filteredOffers: any[] = [];
  loading = true;
  error = '';
  loadingMessage = 'Chargement des offres...';

  // Filtres
  selectedType: 'all' | 'emploi' | 'stage' = 'all';
  selectedLocation = '';
  selectedExperience: 'all' | 'junior' | 'intermediate' | 'senior' | 'expert' = 'all';
  searchTerm = '';

  // Modal pour les détails
  showModal = false;
  selectedJobOffer: JobOffer | null = null;

  constructor(
    private jobService: JobService,
    private router: Router
  ) {}

  ngOnInit() {
    this.loadJobOffers();
  }

  loadJobOffers() {
    this.loading = true;
    this.error = '';
    this.loadingMessage = 'Connexion à l\'API...';

    // Démarrer un timer pour mettre à jour le message
    const messageTimer = setInterval(() => {
      if (this.loading) {
        this.loadingMessage = 'Récupération des données...';
        setTimeout(() => {
          if (this.loading) {
            this.loadingMessage = 'Traitement en cours...';
          }
        }, 2000);
      }
    }, 1000);

    this.jobService.getJobOffers().subscribe({
      next: (response: any) => {
        clearInterval(messageTimer);

        if (response.success) {
          this.jobOffers = response.data;
          this.filteredOffers = [...this.jobOffers];
          this.loadingMessage = `${this.jobOffers.length} offres chargées !`;
          this.applyFilters();

          // Afficher le message de succès pendant 500ms
          setTimeout(() => {
            this.loading = false;
          }, 500);
        } else {
          this.error = 'Erreur lors du chargement des offres';
          this.loading = false;
        }
      },
      error: (err: any) => {
        clearInterval(messageTimer);
        console.error('Erreur:', err);
        this.error = 'Impossible de charger les offres d\'emploi';
        this.loading = false;
      }
    });
  }

  applyFilters() {
    this.filteredOffers = this.jobOffers.filter(offer => {
      // Filtre par type
      if (this.selectedType !== 'all' && offer.type !== this.selectedType) {
        return false;
      }

      // Filtre par niveau d'expérience
      if (this.selectedExperience !== 'all' && offer.experience_level !== this.selectedExperience) {
        return false;
      }

      // Filtre par localisation
      if (this.selectedLocation && !offer.location.toLowerCase().includes(this.selectedLocation.toLowerCase())) {
        return false;
      }

      // Recherche textuelle
      if (this.searchTerm) {
        const searchLower = this.searchTerm.toLowerCase();
        return offer.title.toLowerCase().includes(searchLower) ||
               offer.company_name.toLowerCase().includes(searchLower) ||
               offer.description.toLowerCase().includes(searchLower);
      }

      return true;
    });
  }

  onFilterChange() {
    this.applyFilters();
  }

  applyToJob(jobOffer: JobOffer) {
    // Naviguer vers le formulaire de candidature avec l'ID de l'offre
    this.router.navigate(['/apply'], { queryParams: { jobId: jobOffer.id } });
  }

  viewJobDetails(jobOffer: JobOffer) {
    // Ouvrir le modal avec les détails de l'offre
    this.selectedJobOffer = jobOffer;
    this.showModal = true;
    console.log('Ouverture du modal pour:', jobOffer.title);
  }

  closeModal() {
    this.showModal = false;
    this.selectedJobOffer = null;
  }

  applyFromModal() {
    if (this.selectedJobOffer) {
      this.closeModal();
      this.applyToJob(this.selectedJobOffer);
    }
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

  isDeadlineApproaching(deadline: string | undefined): boolean {
    if (!deadline) return false;
    const deadlineDate = new Date(deadline);
    const today = new Date();
    const diffTime = deadlineDate.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays <= 7 && diffDays > 0;
  }
}
