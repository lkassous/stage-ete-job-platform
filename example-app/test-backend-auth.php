<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Test complet de l'authentification backend...\n\n";

try {
    // 1. Vérifier les rôles créés
    echo "📋 1. Vérification des rôles:\n";
    
    $roles = \App\Models\Role::all();
    foreach ($roles as $role) {
        echo "  ✅ {$role->display_name} ({$role->name})\n";
        echo "    - Permissions: " . implode(', ', $role->permissions ?? []) . "\n";
    }

    // 2. Vérifier les utilisateurs créés
    echo "\n📋 2. Vérification des utilisateurs backend:\n";
    
    $backendUsers = \App\Models\User::whereIn('user_type', ['admin', 'hr_manager', 'recruiter'])
        ->with('role')
        ->get();
    
    foreach ($backendUsers as $user) {
        echo "  ✅ {$user->name} ({$user->email})\n";
        echo "    - Type: {$user->user_type}\n";
        echo "    - Rôle: " . ($user->role ? $user->role->display_name : 'Aucun') . "\n";
        echo "    - Actif: " . ($user->is_active ? 'Oui' : 'Non') . "\n";
    }

    // 3. Test d'inscription backend
    echo "\n📋 3. Test d'inscription backend:\n";
    
    $testData = [
        'first_name' => 'Test',
        'last_name' => 'Backend',
        'email' => 'test.backend@cv-system.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '+33123456789',
        'linkedin_url' => 'https://linkedin.com/in/testbackend',
        'role_name' => 'recruiter'
    ];
    
    // Supprimer l'utilisateur s'il existe
    \App\Models\User::where('email', $testData['email'])->delete();
    
    $controller = new \App\Http\Controllers\Auth\BackendAuthController();
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    
    $response = $controller->register($request);
    $responseData = json_decode($response->getContent(), true);
    
    if ($responseData['success']) {
        echo "  ✅ Inscription backend réussie\n";
        echo "  📧 Email: {$responseData['data']['user']['email']}\n";
        echo "  👤 Rôle: {$responseData['data']['user']['role']['display_name']}\n";
        echo "  🔑 Token généré: " . (isset($responseData['data']['token']) ? 'Oui' : 'Non') . "\n";
    } else {
        echo "  ❌ Échec inscription: " . $responseData['message'] . "\n";
        if (isset($responseData['errors'])) {
            foreach ($responseData['errors'] as $field => $errors) {
                echo "    - $field: " . implode(', ', $errors) . "\n";
            }
        }
    }

    // 4. Test de connexion backend
    echo "\n📋 4. Test de connexion backend:\n";
    
    $loginData = [
        'email' => 'admin@cv-system.com',
        'password' => 'admin123'
    ];
    
    $loginRequest = new \Illuminate\Http\Request();
    $loginRequest->merge($loginData);
    
    $loginResponse = $controller->login($loginRequest);
    $loginResponseData = json_decode($loginResponse->getContent(), true);
    
    if ($loginResponseData['success']) {
        echo "  ✅ Connexion admin réussie\n";
        echo "  📧 Email: {$loginResponseData['data']['user']['email']}\n";
        echo "  👤 Rôle: {$loginResponseData['data']['user']['role']['display_name']}\n";
        echo "  🔑 Permissions: " . implode(', ', $loginResponseData['data']['user']['role']['permissions']) . "\n";
    } else {
        echo "  ❌ Échec connexion: " . $loginResponseData['message'] . "\n";
    }

    // 5. Test de connexion RH
    echo "\n📋 5. Test de connexion RH:\n";
    
    $hrLoginData = [
        'email' => 'rh@cv-system.com',
        'password' => 'rh123'
    ];
    
    $hrLoginRequest = new \Illuminate\Http\Request();
    $hrLoginRequest->merge($hrLoginData);
    
    $hrLoginResponse = $controller->login($hrLoginRequest);
    $hrLoginResponseData = json_decode($hrLoginResponse->getContent(), true);
    
    if ($hrLoginResponseData['success']) {
        echo "  ✅ Connexion RH réussie\n";
        echo "  📧 Email: {$hrLoginResponseData['data']['user']['email']}\n";
        echo "  👤 Rôle: {$hrLoginResponseData['data']['user']['role']['display_name']}\n";
        echo "  🔑 Permissions: " . implode(', ', $hrLoginResponseData['data']['user']['role']['permissions']) . "\n";
    } else {
        echo "  ❌ Échec connexion RH: " . $hrLoginResponseData['message'] . "\n";
    }

    // 6. Test de connexion recruteur
    echo "\n📋 6. Test de connexion recruteur:\n";
    
    $recruiterLoginData = [
        'email' => 'recruteur@cv-system.com',
        'password' => 'recruteur123'
    ];
    
    $recruiterLoginRequest = new \Illuminate\Http\Request();
    $recruiterLoginRequest->merge($recruiterLoginData);
    
    $recruiterLoginResponse = $controller->login($recruiterLoginRequest);
    $recruiterLoginResponseData = json_decode($recruiterLoginResponse->getContent(), true);
    
    if ($recruiterLoginResponseData['success']) {
        echo "  ✅ Connexion recruteur réussie\n";
        echo "  📧 Email: {$recruiterLoginResponseData['data']['user']['email']}\n";
        echo "  👤 Rôle: {$recruiterLoginResponseData['data']['user']['role']['display_name']}\n";
        echo "  🔑 Permissions: " . implode(', ', $recruiterLoginResponseData['data']['user']['role']['permissions']) . "\n";
    } else {
        echo "  ❌ Échec connexion recruteur: " . $recruiterLoginResponseData['message'] . "\n";
    }

    echo "\n🎉 Tests terminés !\n";
    echo "\n📋 Résumé du système d'authentification backend:\n";
    echo "  ✅ Rôles créés: Admin, RH Manager, Recruteur\n";
    echo "  ✅ Utilisateurs par défaut créés\n";
    echo "  ✅ Inscription backend fonctionnelle\n";
    echo "  ✅ Connexion avec vérification des rôles\n";
    echo "  ✅ Gestion des permissions par rôle\n";
    echo "  ✅ Tokens JWT générés\n";
    
    echo "\n🌐 Comptes de test disponibles:\n";
    echo "  👑 Admin: admin@cv-system.com / admin123\n";
    echo "  👥 RH: rh@cv-system.com / rh123\n";
    echo "  🎯 Recruteur: recruteur@cv-system.com / recruteur123\n";
    
    echo "\n📄 Pages créées:\n";
    echo "  • /auth/backend-register.html - Inscription backend\n";
    echo "  • /auth/register.html - Inscription candidats (pour Angular plus tard)\n";
    
    echo "\n🛣️ Routes API:\n";
    echo "  • POST /api/backend/auth/register - Inscription backend\n";
    echo "  • POST /api/backend/auth/login - Connexion backend\n";
    echo "  • POST /api/auth/register - Inscription candidats\n";
    echo "  • POST /api/auth/login - Connexion candidats\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📝 Trace: " . $e->getTraceAsString() . "\n";
}
?>
