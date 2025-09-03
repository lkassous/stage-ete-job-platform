import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { environment } from '../../environments/environment';

export interface JobOffer {
  id: number;
  title: string;
  type: 'emploi' | 'stage';
  description: string;
  requirements: string;
  location: string;
  contract_type: 'CDI' | 'CDD' | 'Stage' | 'Freelance' | 'Alternance';
  salary_range?: string;
  company_name: string;
  company_description?: string;
  experience_level: 'junior' | 'intermediate' | 'senior' | 'expert';
  skills_required?: string[];
  application_deadline?: string;
  status: 'active' | 'inactive' | 'closed';
  positions_available: number;
  contact_email?: string;
  created_at: string;
  updated_at: string;
}

export interface JobOffersResponse {
  success: boolean;
  data: JobOffer[];
  total: number;
}

export interface JobOfferResponse {
  success: boolean;
  data: JobOffer;
}

@Injectable({
  providedIn: 'root'
})
export class JobOfferSimpleService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  /**
   * Récupérer toutes les offres d'emploi actives
   */
  getJobOffers(): Observable<JobOffersResponse> {
    console.log('=== APPEL API JOB OFFERS ===');
    console.log('URL complète:', `${this.apiUrl}/job-offers-public`);

    return this.http.get<JobOffersResponse>(`${this.apiUrl}/job-offers-public`)
      .pipe(
        map(response => {
          console.log('=== RÉPONSE API REÇUE ===');
          console.log('Response:', response);
          return response;
        }),
        catchError(error => {
          console.error('=== ERREUR API JOB OFFERS ===');
          console.error('Error object:', error);
          console.error('Error status:', error.status);
          console.error('Error message:', error.message);
          console.error('Error URL:', error.url);
          return throwError(() => error);
        })
      );
  }

  /**
   * Récupérer une offre d'emploi spécifique
   */
  getJobOffer(id: number): Observable<JobOfferResponse> {
    return this.http.get<JobOfferResponse>(`${this.apiUrl}/job-offers/${id}`);
  }
}
