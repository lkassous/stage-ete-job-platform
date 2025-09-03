<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🐘 Test de connexion PostgreSQL...\n\n";

try {
    // Test de connexion
    echo "🔌 Test de connexion à la base de données...\n";
    $connection = DB::connection();
    $pdo = $connection->getPdo();
    
    echo "✅ Connexion réussie !\n";
    echo "📊 Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    echo "🏷️ Version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    
    // Test d'une requête simple
    echo "\n🧪 Test d'une requête simple...\n";
    $result = DB::select('SELECT version() as version');
    echo "✅ Requête réussie !\n";
    echo "🐘 PostgreSQL: " . $result[0]->version . "\n";
    
    // Vérifier si la base de données existe
    echo "\n🗄️ Vérification de la base de données...\n";
    $dbName = config('database.connections.pgsql.database');
    echo "📋 Base de données configurée: $dbName\n";
    
    // Lister les tables existantes
    echo "\n📋 Tables existantes:\n";
    $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
    
    if (empty($tables)) {
        echo "⚠️ Aucune table trouvée (normal pour une nouvelle base)\n";
    } else {
        foreach ($tables as $table) {
            echo "  - " . $table->tablename . "\n";
        }
    }
    
    echo "\n🎉 PostgreSQL est prêt pour Laravel !\n";
    echo "🚀 Tu peux maintenant lancer les migrations.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
    echo "\n🔧 Vérifications à faire:\n";
    echo "  1. PostgreSQL est-il démarré ?\n";
    echo "  2. La base de données 'laravel_passport_auth' existe-t-elle ?\n";
    echo "  3. Le mot de passe dans .env est-il correct ?\n";
    echo "  4. Le port 5432 est-il accessible ?\n";
}
