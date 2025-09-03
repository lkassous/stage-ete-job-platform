<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Test des routes API...\n\n";

try {
    // Test de la route health
    echo "📋 Routes disponibles:\n";
    
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        $methods = implode('|', $route->methods());
        $name = $route->getName() ?? 'N/A';
        
        if (str_starts_with($uri, 'api/')) {
            echo "  {$methods} /{$uri} -> {$name}\n";
        }
    }
    
    echo "\n✅ Routes API chargées avec succès !\n";
    
    // Test de création d'un utilisateur admin
    echo "\n👤 Création d'un utilisateur admin de test...\n";
    
    $admin = \App\Models\User::firstOrCreate(
        ['email' => 'admin@cv-system.com'],
        [
            'name' => 'Admin System',
            'first_name' => 'Admin',
            'last_name' => 'System',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'user_type' => 'admin',
            'is_active' => true,
        ]
    );
    
    echo "✅ Admin créé: {$admin->email}\n";
    
    // Test de création d'un candidat
    echo "\n👤 Création d'un candidat de test...\n";
    
    $candidate = \App\Models\User::firstOrCreate(
        ['email' => 'candidate@example.com'],
        [
            'name' => 'John Doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'phone' => '+33123456789',
            'linkedin_url' => 'https://linkedin.com/in/johndoe',
            'user_type' => 'candidate',
            'is_active' => true,
        ]
    );
    
    echo "✅ Candidat créé: {$candidate->email}\n";
    
    echo "\n🎉 Configuration terminée !\n";
    echo "\n📋 Comptes de test créés:\n";
    echo "  Admin: admin@cv-system.com / admin123\n";
    echo "  Candidat: candidate@example.com / password123\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📝 Trace: " . $e->getTraceAsString() . "\n";
}
?>
