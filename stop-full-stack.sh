#!/bin/bash

# Script d'arrêt pour la stack complète CV Filtering System

echo "🛑 Arrêt du CV Filtering System - Full Stack"
echo "============================================="

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

# Vérifier si Docker Compose est disponible
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose n'est pas installé."
    exit 1
fi

# Arrêter tous les services
print_status "Arrêt de tous les services..."
docker-compose -f docker-compose.full-stack.yml down

# Optionnel: Supprimer les volumes (décommentez si vous voulez supprimer les données)
# print_warning "Suppression des volumes (données de la base de données)..."
# docker-compose -f docker-compose.full-stack.yml down -v

# Optionnel: Supprimer les images (décommentez si vous voulez supprimer les images)
# print_warning "Suppression des images Docker..."
# docker-compose -f docker-compose.full-stack.yml down --rmi all

# Afficher les conteneurs restants
print_status "Conteneurs Docker restants:"
docker ps -a --filter "name=cv-"

# Nettoyer les ressources inutilisées
print_status "Nettoyage des ressources Docker inutilisées..."
docker system prune -f

print_success "✅ Tous les services ont été arrêtés avec succès!"
print_status "💡 Pour redémarrer, utilisez: ./start-full-stack.sh"
