#!/bin/bash

# Attendre que PostgreSQL soit prêt
echo "Attente de PostgreSQL..."
until php -r "
try {
    \$pdo = new PDO('pgsql:host=postgres;port=5432;dbname=laravel_db', 'laravel_user', 'laravel_password');
    echo 'PostgreSQL est prêt!' . PHP_EOL;
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
"; do
  echo "En attente de PostgreSQL..."
  sleep 2
done

# Copier le fichier .env si il n'existe pas
if [ ! -f .env ]; then
    cp .env.example .env
    echo "Fichier .env créé à partir de .env.example"
fi

# Générer la clé d'application si elle n'existe pas
if ! grep -q "APP_KEY=" .env || [ -z "$(grep APP_KEY= .env | cut -d '=' -f2)" ]; then
    php artisan key:generate
    echo "Clé d'application générée"
fi

# Exécuter les migrations
php artisan migrate --force

# Nettoyer et optimiser le cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Créer un lien symbolique pour le stockage
php artisan storage:link

# Démarrer Apache en mode foreground
exec apache2-foreground
