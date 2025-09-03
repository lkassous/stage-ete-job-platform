import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { DOCUMENT } from '@angular/common';

import { CvSubmission } from './cv-submission';
import { CandidateService } from '../../services/candidate.service';
import { NotificationService } from '../../services/notification.service';

describe('CvSubmission', () => {
  let component: CvSubmission;
  let fixture: ComponentFixture<CvSubmission>;
  let candidateService: jasmine.SpyObj<CandidateService>;
  let notificationService: jasmine.SpyObj<NotificationService>;

  beforeEach(async () => {
    const candidateServiceSpy = jasmine.createSpyObj('CandidateService', ['submitCandidate']);
    const notificationServiceSpy = jasmine.createSpyObj('NotificationService', ['showSuccess', 'showError']);

    await TestBed.configureTestingModule({
      imports: [
        CvSubmission,
        ReactiveFormsModule,
        NoopAnimationsModule,
        HttpClientTestingModule
      ],
      providers: [
        { provide: CandidateService, useValue: candidateServiceSpy },
        { provide: NotificationService, useValue: notificationServiceSpy },
        { provide: DOCUMENT, useValue: document }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(CvSubmission);
    component = fixture.componentInstance;
    candidateService = TestBed.inject(CandidateService) as jasmine.SpyObj<CandidateService>;
    notificationService = TestBed.inject(NotificationService) as jasmine.SpyObj<NotificationService>;
    
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should initialize form with empty values', () => {
    expect(component.candidateForm.get('nom')?.value).toBe('');
    expect(component.candidateForm.get('prenom')?.value).toBe('');
    expect(component.candidateForm.get('email')?.value).toBe('');
    expect(component.candidateForm.get('telephone')?.value).toBe('');
    expect(component.candidateForm.get('linkedin_url')?.value).toBe('');
  });

  it('should validate required fields', () => {
    const nomControl = component.candidateForm.get('nom');
    const prenomControl = component.candidateForm.get('prenom');
    const emailControl = component.candidateForm.get('email');
    const telephoneControl = component.candidateForm.get('telephone');

    expect(nomControl?.hasError('required')).toBeTruthy();
    expect(prenomControl?.hasError('required')).toBeTruthy();
    expect(emailControl?.hasError('required')).toBeTruthy();
    expect(telephoneControl?.hasError('required')).toBeTruthy();
  });

  it('should validate email format', () => {
    const emailControl = component.candidateForm.get('email');
    
    emailControl?.setValue('invalid-email');
    expect(emailControl?.hasError('email')).toBeTruthy();
    
    emailControl?.setValue('valid@email.com');
    expect(emailControl?.hasError('email')).toBeFalsy();
  });

  it('should validate minimum length for nom and prenom', () => {
    const nomControl = component.candidateForm.get('nom');
    const prenomControl = component.candidateForm.get('prenom');
    
    nomControl?.setValue('A');
    prenomControl?.setValue('B');
    
    expect(nomControl?.hasError('minlength')).toBeTruthy();
    expect(prenomControl?.hasError('minlength')).toBeTruthy();
    
    nomControl?.setValue('Alice');
    prenomControl?.setValue('Bob');
    
    expect(nomControl?.hasError('minlength')).toBeFalsy();
    expect(prenomControl?.hasError('minlength')).toBeFalsy();
  });

  it('should validate PDF file type', () => {
    const pdfFile = new File(['test'], 'test.pdf', { type: 'application/pdf' });
    const txtFile = new File(['test'], 'test.txt', { type: 'text/plain' });
    
    expect(component.isValidPdfFile(pdfFile)).toBeTruthy();
    expect(component.isValidPdfFile(txtFile)).toBeFalsy();
  });

  it('should reset form correctly', () => {
    // Set some values
    component.candidateForm.patchValue({
      nom: 'Test',
      prenom: 'User',
      email: 'test@example.com'
    });
    component.cvFile = new File(['test'], 'cv.pdf', { type: 'application/pdf' });
    component.lettreFile = new File(['test'], 'lettre.pdf', { type: 'application/pdf' });
    
    // Reset form
    component.resetForm();
    
    // Check if form is reset
    expect(component.candidateForm.get('nom')?.value).toBeNull();
    expect(component.candidateForm.get('prenom')?.value).toBeNull();
    expect(component.candidateForm.get('email')?.value).toBeNull();
    expect(component.cvFile).toBeNull();
    expect(component.lettreFile).toBeNull();
  });

  it('should return correct error messages', () => {
    expect(component.getErrorMessage('nom')).toBe('nom est requis');
    
    const emailControl = component.candidateForm.get('email');
    emailControl?.setValue('invalid-email');
    emailControl?.markAsTouched();
    expect(component.getErrorMessage('email')).toBe('Email invalide');
  });
});
