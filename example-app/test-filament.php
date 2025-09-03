<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🎨 Test des ressources Filament...\n\n";

try {
    // Vérifier que Filament est installé
    if (class_exists('Filament\Resources\Resource')) {
        echo "✅ Filament est installé et disponible\n";
    } else {
        echo "❌ Filament n'est pas disponible\n";
        exit;
    }

    // Vérifier les ressources
    echo "\n📋 Ressources Filament créées:\n";
    
    $resources = [
        'App\Filament\Resources\UserResource',
        'App\Filament\Resources\CandidateApplicationResource',
    ];
    
    foreach ($resources as $resource) {
        if (class_exists($resource)) {
            echo "  ✅ $resource\n";
        } else {
            echo "  ❌ $resource (non trouvée)\n";
        }
    }

    // Vérifier les widgets
    echo "\n📊 Widgets Filament créés:\n";
    
    $widgets = [
        'App\Filament\Widgets\StatsOverview',
        'App\Filament\Widgets\ApplicationsChart',
    ];
    
    foreach ($widgets as $widget) {
        if (class_exists($widget)) {
            echo "  ✅ $widget\n";
        } else {
            echo "  ❌ $widget (non trouvé)\n";
        }
    }

    // Vérifier les pages
    echo "\n📄 Pages Filament créées:\n";
    
    $pages = [
        'App\Filament\Resources\UserResource\Pages\ListUsers',
        'App\Filament\Resources\UserResource\Pages\CreateUser',
        'App\Filament\Resources\UserResource\Pages\EditUser',
        'App\Filament\Resources\UserResource\Pages\ViewUser',
        'App\Filament\Resources\CandidateApplicationResource\Pages\ListCandidateApplications',
        'App\Filament\Resources\CandidateApplicationResource\Pages\CreateCandidateApplication',
        'App\Filament\Resources\CandidateApplicationResource\Pages\EditCandidateApplication',
        'App\Filament\Resources\CandidateApplicationResource\Pages\ViewCandidateApplication',
    ];
    
    foreach ($pages as $page) {
        if (class_exists($page)) {
            echo "  ✅ " . basename(str_replace('\\', '/', $page)) . "\n";
        } else {
            echo "  ❌ " . basename(str_replace('\\', '/', $page)) . " (non trouvée)\n";
        }
    }

    echo "\n🎉 Interface Filament prête !\n";
    echo "\n📋 Pour accéder à l'interface admin:\n";
    echo "  1. Démarre le serveur: php artisan serve\n";
    echo "  2. Va sur: http://localhost:8000/admin\n";
    echo "  3. Connecte-toi avec: admin@cv-system.com / admin123\n";
    
    echo "\n🔧 Fonctionnalités disponibles:\n";
    echo "  • Gestion des utilisateurs (voir, créer, modifier, bloquer/débloquer)\n";
    echo "  • Gestion des candidatures (voir, créer, modifier, analyser)\n";
    echo "  • Statistiques en temps réel\n";
    echo "  • Interface responsive et moderne\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📝 Trace: " . $e->getTraceAsString() . "\n";
}
?>
