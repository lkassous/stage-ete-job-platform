export interface Candidate {
  id?: number;
  nom: string;
  prenom: string;
  email: string;
  telephone: string;
  linkedin_url?: string;
  cv_file?: File;
  lettre_motivation_file?: File;
  created_at?: string;
  updated_at?: string;
}

export interface CandidateSubmission {
  nom: string;
  prenom: string;
  email: string;
  telephone: string;
  linkedin_url?: string;
  job_offer_id: number;
  cv_file: File;
  lettre_motivation_file: File;
}

export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data?: T;
  errors?: any;
}
