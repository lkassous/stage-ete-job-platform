import { Routes } from '@angular/router';
import { JobOffersComponent } from './components/job-offers/job-offers';
import { CvSubmission } from './components/cv-submission/cv-submission';

export const routes: Routes = [
  { path: '', redirectTo: '/offers', pathMatch: 'full' },
  { path: 'offers', component: JobOffersComponent },
  { path: 'apply', component: CvSubmission },
  { path: '**', redirectTo: '/offers' }
];
