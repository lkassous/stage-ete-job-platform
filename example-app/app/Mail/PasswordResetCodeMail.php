<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class PasswordResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $resetCode;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $resetCode)
    {
        $this->user = $user;
        $this->resetCode = $resetCode;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Code de réinitialisation de mot de passe - Job Platform',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.password-reset-code',
            with: [
                'user' => $this->user,
                'resetCode' => $this->resetCode,
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
