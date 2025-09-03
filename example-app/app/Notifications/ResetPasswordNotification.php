<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPasswordNotification
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        // Construire l'URL de réinitialisation de manière simple
        $baseUrl = rtrim(config('app.url'), '/');
        $resetUrl = $baseUrl . '/password/reset/' . $this->token . '?email=' . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe - ' . config('app.name'))
            ->view('emails.password-reset', [
                'url' => $resetUrl,
                'user' => $notifiable,
                'token' => $this->token
            ]);
    }
}
