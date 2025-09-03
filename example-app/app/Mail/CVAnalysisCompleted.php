<?php

namespace App\Mail;

use App\Models\Candidate;
use App\Models\CVAnalysis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CVAnalysisCompleted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Candidate $candidate,
        public CVAnalysis $analysis
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🤖 Analyse IA de votre CV terminée - CV Filtering System',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.candidate.analysis-completed',
            with: [
                'candidate' => $this->candidate,
                'analysis' => $this->analysis,
                'analysisDate' => $this->analysis->analyzed_at?->format('d/m/Y à H:i') ?? 'Récemment',
                'score' => $this->analysis->job_match_score ?? 'N/A',
                'rating' => $this->analysis->overall_rating ?? 'En cours',
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
