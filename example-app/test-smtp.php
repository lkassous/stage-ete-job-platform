<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "📧 Test de configuration SMTP...\n\n";

try {
    // Afficher la configuration
    echo "⚙️ Configuration actuelle:\n";
    echo "Driver: " . config('mail.default') . "\n";
    echo "Host: " . config('mail.mailers.smtp.host') . "\n";
    echo "Port: " . config('mail.mailers.smtp.port') . "\n";
    echo "Username: " . config('mail.mailers.smtp.username') . "\n";
    echo "Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
    echo "From: " . config('mail.from.address') . "\n\n";
    
    // Test 1: Email simple
    echo "🧪 Test 1: Email simple...\n";
    
    Mail::raw('Ceci est un test d\'email depuis Laravel avec SMTP.', function ($message) {
        $message->to(config('mail.from.address'))
                ->subject('Test SMTP Laravel');
    });
    
    echo "✅ Email simple envoyé !\n\n";
    
    // Test 2: Reset password
    echo "🧪 Test 2: Reset password...\n";
    
    $email = 'test@example.com'; // Utilise un email d'utilisateur existant
    $status = Password::sendResetLink(['email' => $email]);
    
    if ($status === Password::RESET_LINK_SENT) {
        echo "✅ Email de reset envoyé avec succès !\n";
        echo "📋 Status: $status\n";
    } else {
        echo "❌ Erreur lors de l'envoi du reset\n";
        echo "📋 Status: $status\n";
        
        if ($status === Password::INVALID_USER) {
            echo "⚠️ Utilisateur non trouvé. Créons-le...\n";
            
            // Créer l'utilisateur s'il n'existe pas
            \App\Models\User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Test User',
                    'password' => \Illuminate\Support\Facades\Hash::make('password123')
                ]
            );
            
            echo "✅ Utilisateur créé. Retry...\n";
            $status = Password::sendResetLink(['email' => $email]);
            
            if ($status === Password::RESET_LINK_SENT) {
                echo "✅ Email de reset envoyé après création utilisateur !\n";
            } else {
                echo "❌ Erreur persistante: $status\n";
            }
        }
    }
    
    echo "\n🎉 Tests terminés !\n";
    echo "📬 Vérifie ta boîte mail : " . config('mail.from.address') . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📝 Trace: " . $e->getTraceAsString() . "\n";
    
    // Suggestions de dépannage
    echo "\n🔧 Vérifications à faire:\n";
    echo "1. Email et mot de passe d'application Gmail corrects\n";
    echo "2. Authentification 2FA activée sur Gmail\n";
    echo "3. Mot de passe d'application généré (pas le mot de passe normal)\n";
    echo "4. Connexion internet active\n";
}
