#!/bin/bash

# Script de démarrage pour la stack complète CV Filtering System
# Frontend Angular + Backend Laravel + PostgreSQL + Nginx

echo "🚀 Démarrage du CV Filtering System - Full Stack"
echo "=================================================="

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages colorés
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Vérifier si Docker est installé
if ! command -v docker &> /dev/null; then
    print_error "Docker n'est pas installé. Veuillez installer Docker Desktop."
    exit 1
fi

# Vérifier si Docker Compose est installé
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose n'est pas installé. Veuillez installer Docker Compose."
    exit 1
fi

print_status "Vérification de Docker..."
if ! docker info &> /dev/null; then
    print_error "Docker n'est pas en cours d'exécution. Veuillez démarrer Docker Desktop."
    exit 1
fi

print_success "Docker est prêt ✓"

# Arrêter les conteneurs existants s'ils existent
print_status "Arrêt des conteneurs existants..."
docker-compose -f docker-compose.full-stack.yml down --remove-orphans

# Nettoyer les images orphelines (optionnel)
print_status "Nettoyage des ressources Docker..."
docker system prune -f

# Construire et démarrer les services
print_status "Construction et démarrage des services..."
print_warning "Cela peut prendre plusieurs minutes lors du premier démarrage..."

# Démarrer la base de données en premier
print_status "Démarrage de la base de données PostgreSQL..."
docker-compose -f docker-compose.full-stack.yml up -d database

# Attendre que la base de données soit prête
print_status "Attente de la base de données..."
sleep 10

# Vérifier si la base de données est prête
until docker-compose -f docker-compose.full-stack.yml exec -T database pg_isready -U cv_user -d cv_filtering_db; do
    print_status "En attente de PostgreSQL..."
    sleep 2
done

print_success "Base de données PostgreSQL prête ✓"

# Démarrer le backend Laravel
print_status "Démarrage du backend Laravel..."
docker-compose -f docker-compose.full-stack.yml up -d backend

# Attendre que le backend soit prêt
print_status "Attente du backend Laravel..."
sleep 15

# Vérifier si le backend est prêt
backend_ready=false
for i in {1..30}; do
    if curl -f http://localhost:8000/api/health &> /dev/null; then
        backend_ready=true
        break
    fi
    print_status "En attente du backend Laravel... ($i/30)"
    sleep 2
done

if [ "$backend_ready" = true ]; then
    print_success "Backend Laravel prêt ✓"
else
    print_warning "Le backend Laravel met du temps à démarrer, continuons..."
fi

# Démarrer le frontend Angular
print_status "Démarrage du frontend Angular..."
docker-compose -f docker-compose.full-stack.yml up -d frontend

# Attendre que le frontend soit prêt
print_status "Attente du frontend Angular..."
sleep 10

# Démarrer Nginx (reverse proxy)
print_status "Démarrage du reverse proxy Nginx..."
docker-compose -f docker-compose.full-stack.yml up -d nginx

# Attendre que tous les services soient prêts
print_status "Vérification finale des services..."
sleep 5

# Afficher le statut des services
print_status "Statut des services:"
docker-compose -f docker-compose.full-stack.yml ps

echo ""
echo "🎉 Démarrage terminé !"
echo "====================="
echo ""
print_success "✅ Application disponible sur:"
echo "   🌐 Frontend (via Nginx): http://localhost"
echo "   🔧 Backend API: http://localhost/api"
echo "   📊 Frontend direct: http://localhost:4200"
echo "   🚀 Backend direct: http://localhost:8000"
echo "   🗄️  Base de données: localhost:5433"
echo ""
print_status "📋 Informations de connexion à la base de données:"
echo "   Host: localhost"
echo "   Port: 5433"
echo "   Database: cv_filtering_db"
echo "   Username: cv_user"
echo "   Password: cv_password"
echo ""
print_status "🛠️  Commandes utiles:"
echo "   Voir les logs: docker-compose -f docker-compose.full-stack.yml logs -f"
echo "   Arrêter: docker-compose -f docker-compose.full-stack.yml down"
echo "   Redémarrer: docker-compose -f docker-compose.full-stack.yml restart"
echo ""
print_warning "⚠️  Si vous rencontrez des problèmes, vérifiez les logs avec:"
echo "   docker-compose -f docker-compose.full-stack.yml logs [service_name]"
echo ""
print_success "🚀 Votre système de filtrage CV est maintenant opérationnel !"
