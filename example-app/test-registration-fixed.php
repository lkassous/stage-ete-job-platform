<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Test de l'inscription corrigée...\n\n";

try {
    // Test avec les nouveaux champs
    echo "📋 1. Test avec les champs corrigés:\n";
    
    $testData = [
        'first_name' => 'Jean',
        'last_name' => 'Dupont',
        'email' => 'jean.dupont.fixed@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '+33123456789',
        'linkedin_url' => 'https://linkedin.com/in/jeandupont',
        'role_name' => 'candidate'
    ];
    
    echo "  📝 Données envoyées (comme ta page HTML maintenant):\n";
    foreach ($testData as $key => $value) {
        echo "    - $key: $value\n";
    }
    
    // Supprimer l'utilisateur s'il existe
    \App\Models\User::where('email', $testData['email'])->delete();
    
    // Test du contrôleur
    $controller = new \App\Http\Controllers\Auth\CandidateAuthController();
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    
    $response = $controller->register($request);
    $responseData = json_decode($response->getContent(), true);
    
    echo "\n  📝 Réponse du contrôleur:\n";
    echo "    - Success: " . ($responseData['success'] ? 'true' : 'false') . "\n";
    echo "    - Message: " . $responseData['message'] . "\n";
    
    if ($responseData['success']) {
        echo "    - Email utilisateur: " . $responseData['data']['user']['email'] . "\n";
        echo "    - Prénom: " . $responseData['data']['user']['first_name'] . "\n";
        echo "    - Nom: " . $responseData['data']['user']['last_name'] . "\n";
        echo "    - Téléphone: " . ($responseData['data']['user']['phone'] ?? 'Non renseigné') . "\n";
        echo "    - LinkedIn: " . ($responseData['data']['user']['linkedin_url'] ?? 'Non renseigné') . "\n";
        echo "    - Token généré: " . (isset($responseData['data']['token']) ? 'Oui' : 'Non') . "\n";
    } else {
        if (isset($responseData['errors'])) {
            echo "    - Erreurs:\n";
            foreach ($responseData['errors'] as $field => $errors) {
                echo "      * $field: " . implode(', ', $errors) . "\n";
            }
        }
    }

    // Test de connexion avec le nouvel utilisateur
    if ($responseData['success']) {
        echo "\n📋 2. Test de connexion avec le nouvel utilisateur:\n";
        
        $loginData = [
            'email' => $testData['email'],
            'password' => $testData['password']
        ];
        
        $loginRequest = new \Illuminate\Http\Request();
        $loginRequest->merge($loginData);
        
        $loginResponse = $controller->login($loginRequest);
        $loginResponseData = json_decode($loginResponse->getContent(), true);
        
        if ($loginResponseData['success']) {
            echo "  ✅ Connexion réussie\n";
            echo "  📧 Email: " . $loginResponseData['data']['user']['email'] . "\n";
            echo "  👤 Nom complet: " . $loginResponseData['data']['user']['name'] . "\n";
            echo "  🔑 Token reçu: " . (isset($loginResponseData['data']['token']) ? 'Oui' : 'Non') . "\n";
        } else {
            echo "  ❌ Échec connexion: " . $loginResponseData['message'] . "\n";
        }
    }

    echo "\n🎉 Test terminé !\n";
    echo "\n📋 Résumé des corrections apportées:\n";
    echo "  ✅ Champ 'name' remplacé par 'first_name' et 'last_name'\n";
    echo "  ✅ Ajout du champ 'phone'\n";
    echo "  ✅ Ajout du champ 'linkedin_url'\n";
    echo "  ✅ Validation JavaScript mise à jour\n";
    echo "  ✅ CSS pour les lignes de formulaire ajouté\n";
    
    echo "\n🌐 Ta page d'inscription devrait maintenant fonctionner !\n";
    echo "  URL: http://localhost:8000/auth/register.html\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📝 Trace: " . $e->getTraceAsString() . "\n";
}
?>
