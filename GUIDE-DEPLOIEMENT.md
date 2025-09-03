# 🚀 Guide de Déploiement - Système de Gestion CV

## 📋 Vue d'ensemble du Projet

**Système complet de gestion de candidatures** avec :
- ✅ **Frontend Angular** : Interface utilisateur moderne
- ✅ **Backend Laravel** : API REST avec authentification
- ✅ **Base de données PostgreSQL** : Stockage des données
- ✅ **MailHog** : Gestion des emails de test
- ✅ **Docker** : Containerisation complète

---

## 🏗️ Architecture du Système

### **Ports utilisés :**
- **Frontend Angular** : http://localhost:9000
- **Backend Laravel** : http://localhost:8000
- **MailHog (Emails)** : http://localhost:8025
- **PostgreSQL** : Port 5432 (interne)

### **Technologies :**
- **Frontend** : Angular 18, TypeScript, Bootstrap
- **Backend** : Laravel 11, PHP 8.2
- **Base de données** : PostgreSQL 15
- **Containerisation** : Docker & Docker Compose

---

## 🚀 Déploiement Rapide

### **Prérequis :**
- Docker Desktop installé
- Git installé
- Port 9000, 8000, 8025 disponibles

### **Commandes de déploiement :**

```bash
# 1. Cloner le projet
git clone [URL_DU_REPO]
cd stage_été

# 2. Démarrer tous les services
cd example-app
docker-compose up -d

# 3. Attendre 30 secondes puis accéder à :
# Frontend : http://localhost:9000
# Backend : http://localhost:8000/dashboard.html
# Emails : http://localhost:8025
```

---

## 🎯 Fonctionnalités Principales

### **👥 Pour les Candidats :**
- ✅ **Consultation des offres** : http://localhost:9000/offers
- ✅ **Candidature en ligne** : Upload CV + Lettre de motivation
- ✅ **Email de confirmation** automatique

### **👨‍💼 Pour les Administrateurs :**
- ✅ **Dashboard complet** : http://localhost:8000/dashboard.html
- ✅ **Gestion des offres** : Création, modification, suppression
- ✅ **Gestion des candidatures** : Consultation, filtrage, pagination
- ✅ **Système de rôles** : 6 rôles avec permissions
- ✅ **Authentification** : Login/logout sécurisé

---

## 🔧 Configuration Avancée

### **Variables d'environnement (.env) :**
```
APP_URL=http://localhost:8000
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_DATABASE=cv_filtering_db
MAIL_MAILER=smtp
MAIL_HOST=mailhog
```

### **Comptes par défaut :**
- **Admin** : admin@example.com / password
- **RH** : hr@example.com / password
- **Manager** : manager@example.com / password

---

## 📊 Base de Données

### **Tables principales :**
- `job_offers` : Offres d'emploi
- `candidates` : Candidatures
- `users` : Utilisateurs du système
- `roles` : Système de rôles et permissions
- `cv_analyses` : Analyses automatiques des CV

### **Données de test :**
- ✅ **3 offres d'emploi** pré-créées
- ✅ **6 rôles** avec permissions
- ✅ **Utilisateurs de test** pour chaque rôle

---

## 🛠️ Maintenance

### **Commandes utiles :**
```bash
# Arrêter les services
docker-compose down

# Voir les logs
docker-compose logs -f

# Reconstruire après modifications
docker-compose build --no-cache
docker-compose up -d

# Nettoyer Docker
docker system prune -f
```

### **Dépannage :**
- **Port occupé** : Modifier les ports dans docker-compose.yml
- **Problème CORS** : Vérifier config/cors.php
- **Base de données** : Vérifier les variables .env

---

## 📧 Contact

**Développeur** : [Votre nom]
**Email** : [Votre email]
**Date** : Août 2025
