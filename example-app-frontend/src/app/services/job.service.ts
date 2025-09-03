import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { tap } from 'rxjs/operators';

export interface JobOffer {
  id: number;
  title: string;
  type: string;
  description: string;
  requirements: string;
  location: string;
  contract_type: string;
  salary_range?: string;
  company_name: string;
  company_description?: string;
  experience_level: string;
  skills_required?: string[];
  application_deadline?: string;
  status: string;
  positions_available: number;
  contact_email?: string;
  created_at: string;
  updated_at: string;
}

@Injectable({
  providedIn: 'root'
})
export class JobService {
  private apiUrl = 'http://localhost:8000/api';
  private cachedOffers: any = null;
  private cacheTimestamp: number = 0;
  private cacheExpiry = 5 * 60 * 1000; // 5 minutes

  constructor(private http: HttpClient) {}

  getJobOffers(): Observable<any> {
    console.log('=== APPEL API JOB OFFERS AVEC CACHE FRONTEND ===');

    // Vérifier si on a des données en cache et si elles sont encore valides
    const now = Date.now();
    if (this.cachedOffers && (now - this.cacheTimestamp) < this.cacheExpiry) {
      console.log('✅ DONNÉES EN CACHE - Retour immédiat');
      return of(this.cachedOffers);
    }

    console.log('🔄 APPEL API - Mise à jour du cache');
    console.log('URL:', this.apiUrl + '/job-offers-fast');

    return this.http.get(this.apiUrl + '/job-offers-fast').pipe(
      tap((response: any) => {
        // Mettre en cache la réponse
        this.cachedOffers = response;
        this.cacheTimestamp = now;
        console.log('💾 Données mises en cache pour 5 minutes');
      })
    );
  }

  getJobOffer(id: number): Observable<any> {
    return this.http.get(this.apiUrl + '/job-offers/' + id);
  }

  /**
   * Force le rafraîchissement du cache
   */
  refreshCache(): Observable<any> {
    console.log('🔄 RAFRAÎCHISSEMENT FORCÉ DU CACHE');
    this.cachedOffers = null;
    this.cacheTimestamp = 0;
    return this.getJobOffers();
  }

  /**
   * Vide le cache
   */
  clearCache(): void {
    console.log('🗑️ CACHE VIDÉ');
    this.cachedOffers = null;
    this.cacheTimestamp = 0;
  }
}
