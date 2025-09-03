<?php
// Test direct d'envoi d'email
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Password;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Test direct d'envoi d'email...\n\n";

try {
    $email = 'test@example.com';
    echo "📧 Envoi vers: $email\n";
    
    $status = Password::sendResetLink(['email' => $email]);
    
    if ($status === Password::RESET_LINK_SENT) {
        echo "✅ Email envoyé avec succès !\n";
        echo "📋 Status: $status\n";
        
        // Vérifier la configuration mail
        echo "\n⚙️ Configuration mail:\n";
        echo "Driver: " . config('mail.default') . "\n";
        echo "Host: " . config('mail.mailers.smtp.host') . "\n";
        echo "Port: " . config('mail.mailers.smtp.port') . "\n";
        echo "Username: " . config('mail.mailers.smtp.username') . "\n";
        
    } else {
        echo "❌ Erreur lors de l'envoi\n";
        echo "📋 Status: $status\n";
        
        // Afficher les statuts possibles
        echo "\n📝 Statuts possibles:\n";
        echo "- " . Password::RESET_LINK_SENT . " (succès)\n";
        echo "- " . Password::INVALID_USER . " (utilisateur invalide)\n";
        echo "- " . Password::RESET_THROTTLED . " (trop de tentatives)\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "📝 Trace: " . $e->getTraceAsString() . "\n";
}
?>
