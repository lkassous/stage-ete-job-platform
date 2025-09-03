import { Injectable } from '@angular/core';
import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';

@Injectable({
  providedIn: 'root'
})
export class ValidationService {

  constructor() { }

  /**
   * Validateur pour les numéros de téléphone français
   */
  static frenchPhoneValidator(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value) {
        return null;
      }

      const phoneRegex = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/;
      const isValid = phoneRegex.test(control.value.replace(/\s/g, ''));
      
      return isValid ? null : { invalidFrenchPhone: true };
    };
  }

  /**
   * Validateur pour les URLs LinkedIn
   */
  static linkedinUrlValidator(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value) {
        return null; // Optional field
      }

      const linkedinRegex = /^https?:\/\/(www\.)?linkedin\.com\/(in|pub|profile)\/[a-zA-Z0-9-]+\/?$/;
      const isValid = linkedinRegex.test(control.value);
      
      return isValid ? null : { invalidLinkedinUrl: true };
    };
  }

  /**
   * Validateur pour la taille des fichiers
   */
  static fileSizeValidator(maxSizeInMB: number): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value) {
        return null;
      }

      const file = control.value as File;
      const maxSizeInBytes = maxSizeInMB * 1024 * 1024;
      
      if (file.size > maxSizeInBytes) {
        return { 
          fileSizeExceeded: { 
            actualSize: file.size, 
            maxSize: maxSizeInBytes,
            maxSizeMB: maxSizeInMB
          } 
        };
      }
      
      return null;
    };
  }

  /**
   * Validateur pour le type de fichier
   */
  static fileTypeValidator(allowedTypes: string[]): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value) {
        return null;
      }

      const file = control.value as File;
      const isValidType = allowedTypes.includes(file.type);
      
      return isValidType ? null : { 
        invalidFileType: { 
          actualType: file.type, 
          allowedTypes: allowedTypes 
        } 
      };
    };
  }

  /**
   * Obtenir le message d'erreur approprié pour un champ
   */
  getErrorMessage(fieldName: string, errors: ValidationErrors | null): string {
    if (!errors) {
      return '';
    }

    const fieldDisplayName = this.getFieldDisplayName(fieldName);

    if (errors['required']) {
      return `${fieldDisplayName} est requis`;
    }

    if (errors['email']) {
      return 'Adresse email invalide';
    }

    if (errors['minlength']) {
      const requiredLength = errors['minlength'].requiredLength;
      return `${fieldDisplayName} doit contenir au moins ${requiredLength} caractères`;
    }

    if (errors['maxlength']) {
      const requiredLength = errors['maxlength'].requiredLength;
      return `${fieldDisplayName} ne peut pas dépasser ${requiredLength} caractères`;
    }

    if (errors['invalidFrenchPhone']) {
      return 'Numéro de téléphone français invalide (ex: 01 23 45 67 89)';
    }

    if (errors['invalidLinkedinUrl']) {
      return 'URL LinkedIn invalide (ex: https://www.linkedin.com/in/votre-profil)';
    }

    if (errors['fileSizeExceeded']) {
      const maxSizeMB = errors['fileSizeExceeded'].maxSizeMB;
      return `Le fichier ne peut pas dépasser ${maxSizeMB} MB`;
    }

    if (errors['invalidFileType']) {
      const allowedTypes = errors['invalidFileType'].allowedTypes;
      return `Type de fichier non autorisé. Types acceptés: ${allowedTypes.join(', ')}`;
    }

    return 'Valeur invalide';
  }

  /**
   * Obtenir le nom d'affichage d'un champ
   */
  private getFieldDisplayName(fieldName: string): string {
    const displayNames: { [key: string]: string } = {
      'nom': 'Nom',
      'prenom': 'Prénom',
      'email': 'Email',
      'telephone': 'Téléphone',
      'linkedin_url': 'URL LinkedIn',
      'cv_file': 'CV',
      'lettre_motivation_file': 'Lettre de motivation'
    };

    return displayNames[fieldName] || fieldName;
  }

  /**
   * Formater la taille d'un fichier en format lisible
   */
  formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  /**
   * Vérifier si un fichier est un PDF valide
   */
  isValidPDF(file: File): boolean {
    return file.type === 'application/pdf';
  }

  /**
   * Obtenir l'extension d'un fichier
   */
  getFileExtension(filename: string): string {
    return filename.slice((filename.lastIndexOf('.') - 1 >>> 0) + 2);
  }
}
