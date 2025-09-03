<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Mail;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "🧪 Test de configuration mail...\n";
    
    // Test simple d'envoi d'email
    Mail::raw('Test email depuis Laravel vers Mailtrap', function($message) {
        $message->to('lkassous17@gmail.com')
                ->subject('Test Mailtrap - ' . date('Y-m-d H:i:s'));
    });
    
    echo "✅ Email envoyé avec succès !\n";
    echo "📧 Vérifie ton inbox Mailtrap maintenant.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "📝 Détails : " . $e->getTraceAsString() . "\n";
}
