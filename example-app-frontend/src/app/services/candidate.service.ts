import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { environment } from '../../environments/environment';
import { Candidate, CandidateSubmission, ApiResponse } from '../interfaces/candidate.interface';

@Injectable({
  providedIn: 'root'
})
export class CandidateService {
  private apiUrl = environment.apiUrl;

  constructor(
    private http: HttpClient
  ) {}

  /**
   * Soumettre une candidature avec CV et lettre de motivation
   */
  submitCandidate(candidateData: CandidateSubmission): Observable<ApiResponse<Candidate>> {
    console.log('=== DÉBUT SOUMISSION CANDIDATURE ===');
    console.log('Données reçues:', candidateData);
    console.log('URL API:', `${this.apiUrl}/candidates`);
    // Plus de vérification SSR - mode static uniquement

    const formData = new FormData();
    
    // Ajouter les données texte
    formData.append('nom', candidateData.nom);
    formData.append('prenom', candidateData.prenom);
    formData.append('email', candidateData.email);
    formData.append('telephone', candidateData.telephone);

    if (candidateData.linkedin_url) {
      formData.append('linkedin_url', candidateData.linkedin_url);
    }

    // Ajouter l'ID de l'offre d'emploi (OBLIGATOIRE)
    if (candidateData.job_offer_id) {
      formData.append('job_offer_id', candidateData.job_offer_id.toString());
    }

    // Ajouter les fichiers
    formData.append('cv_file', candidateData.cv_file);
    formData.append('lettre_motivation_file', candidateData.lettre_motivation_file);

    // Debug FormData
    console.log('=== CONTENU FORMDATA ===');
    console.log('FormData créé avec les champs candidat');
    console.log('=== ENVOI VERS ===', `${this.apiUrl}/candidates`);

    return this.http.post<ApiResponse<Candidate>>(`${this.apiUrl}/candidates`, formData)
      .pipe(
        catchError(this.handleError)
      );
  }

  /**
   * Récupérer tous les candidats (pour usage futur)
   */
  getCandidates(): Observable<ApiResponse<Candidate[]>> {
    return this.http.get<ApiResponse<Candidate[]>>(`${this.apiUrl}/candidates`)
      .pipe(
        catchError(this.handleError)
      );
  }

  /**
   * Récupérer un candidat par ID (pour usage futur)
   */
  getCandidate(id: number): Observable<ApiResponse<Candidate>> {
    return this.http.get<ApiResponse<Candidate>>(`${this.apiUrl}/candidates/${id}`)
      .pipe(
        catchError(this.handleError)
      );
  }

  /**
   * Gestion des erreurs HTTP
   */
  private handleError(error: HttpErrorResponse) {
    let errorMessage = 'Une erreur est survenue';
    
    if (error.error instanceof ErrorEvent) {
      // Erreur côté client
      errorMessage = `Erreur: ${error.error.message}`;
    } else {
      // Erreur côté serveur
      if (error.error && error.error.message) {
        errorMessage = error.error.message;
      } else {
        errorMessage = `Erreur ${error.status}: ${error.message}`;
      }
    }
    
    return throwError(() => errorMessage);
  }
}
