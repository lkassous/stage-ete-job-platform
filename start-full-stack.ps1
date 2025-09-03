# Script PowerShell pour démarrer la stack complète CV Filtering System
# Frontend Angular + Backend Laravel + PostgreSQL + Nginx

Write-Host "🚀 Démarrage du CV Filtering System - Full Stack" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan

# Fonction pour afficher les messages colorés
function Write-Status {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Blue
}

function Write-Success {
    param([string]$Message)
    Write-Host "[SUCCESS] $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

# Vérifier si Docker est installé
try {
    docker --version | Out-Null
    Write-Success "Docker est installé ✓"
} catch {
    Write-Error "Docker n'est pas installé. Veuillez installer Docker Desktop."
    exit 1
}

# Vérifier si Docker Compose est installé
try {
    docker-compose --version | Out-Null
    Write-Success "Docker Compose est installé ✓"
} catch {
    Write-Error "Docker Compose n'est pas installé. Veuillez installer Docker Compose."
    exit 1
}

# Vérifier si Docker est en cours d'exécution
Write-Status "Vérification de Docker..."
try {
    docker info | Out-Null
    Write-Success "Docker est en cours d'exécution ✓"
} catch {
    Write-Error "Docker n'est pas en cours d'exécution. Veuillez démarrer Docker Desktop."
    exit 1
}

# Arrêter les conteneurs existants s'ils existent
Write-Status "Arrêt des conteneurs existants..."
docker-compose -f docker-compose.full-stack.yml down --remove-orphans

# Nettoyer les ressources Docker
Write-Status "Nettoyage des ressources Docker..."
docker system prune -f

# Construire et démarrer les services
Write-Status "Construction et démarrage des services..."
Write-Warning "Cela peut prendre plusieurs minutes lors du premier démarrage..."

# Démarrer la base de données en premier
Write-Status "Démarrage de la base de données PostgreSQL..."
docker-compose -f docker-compose.full-stack.yml up -d database

# Attendre que la base de données soit prête
Write-Status "Attente de la base de données..."
Start-Sleep -Seconds 10

# Vérifier si la base de données est prête
$dbReady = $false
$attempts = 0
while (-not $dbReady -and $attempts -lt 30) {
    try {
        docker-compose -f docker-compose.full-stack.yml exec -T database pg_isready -U cv_user -d cv_filtering_db | Out-Null
        $dbReady = $true
        Write-Success "Base de données PostgreSQL prête ✓"
    } catch {
        Write-Status "En attente de PostgreSQL... ($($attempts + 1)/30)"
        Start-Sleep -Seconds 2
        $attempts++
    }
}

if (-not $dbReady) {
    Write-Warning "La base de données met du temps à démarrer, continuons..."
}

# Démarrer le backend Laravel
Write-Status "Démarrage du backend Laravel..."
docker-compose -f docker-compose.full-stack.yml up -d backend

# Attendre que le backend soit prêt
Write-Status "Attente du backend Laravel..."
Start-Sleep -Seconds 15

# Vérifier si le backend est prêt
$backendReady = $false
for ($i = 1; $i -le 30; $i++) {
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8000/api/health" -UseBasicParsing -TimeoutSec 5
        if ($response.StatusCode -eq 200) {
            $backendReady = $true
            Write-Success "Backend Laravel prêt ✓"
            break
        }
    } catch {
        Write-Status "En attente du backend Laravel... ($i/30)"
        Start-Sleep -Seconds 2
    }
}

if (-not $backendReady) {
    Write-Warning "Le backend Laravel met du temps à démarrer, continuons..."
}

# Démarrer le frontend Angular
Write-Status "Démarrage du frontend Angular..."
docker-compose -f docker-compose.full-stack.yml up -d frontend

# Attendre que le frontend soit prêt
Write-Status "Attente du frontend Angular..."
Start-Sleep -Seconds 10

# Démarrer Nginx (reverse proxy)
Write-Status "Démarrage du reverse proxy Nginx..."
docker-compose -f docker-compose.full-stack.yml up -d nginx

# Attendre que tous les services soient prêts
Write-Status "Vérification finale des services..."
Start-Sleep -Seconds 5

# Afficher le statut des services
Write-Status "Statut des services:"
docker-compose -f docker-compose.full-stack.yml ps

Write-Host ""
Write-Host "🎉 Démarrage terminé !" -ForegroundColor Cyan
Write-Host "=====================" -ForegroundColor Cyan
Write-Host ""
Write-Success "✅ Application disponible sur:"
Write-Host "   🌐 Frontend (via Nginx): http://localhost" -ForegroundColor White
Write-Host "   🔧 Backend API: http://localhost/api" -ForegroundColor White
Write-Host "   📊 Frontend direct: http://localhost:3000" -ForegroundColor White
Write-Host "   🚀 Backend direct: http://localhost:8000" -ForegroundColor White
Write-Host "   🗄️  Base de données: localhost:5432" -ForegroundColor White
Write-Host ""
Write-Status "📋 Informations de connexion à la base de données:"
Write-Host "   Host: localhost" -ForegroundColor White
Write-Host "   Port: 5432" -ForegroundColor White
Write-Host "   Database: cv_filtering_db" -ForegroundColor White
Write-Host "   Username: cv_user" -ForegroundColor White
Write-Host "   Password: cv_password" -ForegroundColor White
Write-Host ""
Write-Status "🛠️  Commandes utiles:"
Write-Host "   Voir les logs: docker-compose -f docker-compose.full-stack.yml logs -f" -ForegroundColor White
Write-Host "   Arrêter: docker-compose -f docker-compose.full-stack.yml down" -ForegroundColor White
Write-Host "   Redémarrer: docker-compose -f docker-compose.full-stack.yml restart" -ForegroundColor White
Write-Host ""
Write-Warning "⚠️  Si vous rencontrez des problèmes, vérifiez les logs avec:"
Write-Host "   docker-compose -f docker-compose.full-stack.yml logs [service_name]" -ForegroundColor White
Write-Host ""
Write-Success "🚀 Votre système de filtrage CV est maintenant opérationnel !"

# Ouvrir automatiquement le navigateur
Write-Status "Ouverture du navigateur..."
Start-Process "http://localhost"
