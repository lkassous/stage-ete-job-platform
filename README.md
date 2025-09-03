# 🚀 Stage d'été - Plateforme de Gestion d'Offres d'Emploi

## 🎯 Vue d'ensemble du Projet

Plateforme web moderne pour la gestion et la consultation d'offres d'emploi. Les candidats peuvent consulter les offres disponibles, voir les détails dans un modal rapide, et soumettre leur candidature avec CV et lettre de motivation. Interface optimisée avec système de cache pour de meilleures performances.

## ✨ Fonctionnalités Principales

### 🎯 Frontend (Angular)
- **Liste des offres d'emploi** avec filtres par type, localisation, expérience
- **Modal de détails rapide** pour consultation des offres sans rechargement
- **Formulaire de candidature** optimisé avec upload de CV et lettre
- **Interface responsive** adaptée mobile, tablette et desktop
- **Système de cache** côté client pour performances optimales

### 🔧 Backend (Laravel)
- **API RESTful** pour la gestion des offres d'emploi
- **Système de candidatures** avec validation et stockage sécurisé
- **Base de données PostgreSQL** avec indexation optimisée
- **Endpoints optimisés** avec cache pour réduire les temps de réponse

## 📁 Structure du Projet

Ce projet est composé de **deux applications distinctes et séparées** :

### 🔧 Backend (Laravel) - `example-app/`
- **Framework** : Laravel 12
- **Base de données** : PostgreSQL
- **Admin Panel** : Laravel Filament
- **API** : REST API pour l'intégration avec le frontend
- **IA** : Intégration ChatGPT API (OpenAI)
- **Authentification** : Email/mot de passe

### 🎨 Frontend (Angular) - `example-app-frontend/`
- **Framework** : Angular
- **Interface** : Formulaire de soumission de CV responsive
- **Communication** : Consomme l'API Laravel
- **UI/UX** : Interface utilisateur moderne et intuitive

## 🚀 Fonctionnalités Principales

### 1. 🏠 Dashboard Admin (Laravel Filament)
- Connexion sécurisée pour les administrateurs
- Visualisation des CV téléchargés
- Affichage des résumés générés par l'IA
- Gestion des candidatures

### 2. 📤 Page de Soumission de CV (Angular)
Formulaire pour les candidats avec :
- **Nom** (Last Name)
- **Prénom** (First Name)
- **Email**
- **Téléphone**
- **URL LinkedIn**
- **Fichier CV** (PDF)
- **Lettre de motivation** (PDF)
- Validation et gestion des fichiers

### 3. 🧠 Analyse IA des CV
- Envoi du contenu vers l'API ChatGPT
- Extraction automatique :
  - **Résumé du profil**
  - **Compétences clés**
  - **Formation**
  - **Expérience**
  - **Adéquation au poste**
- Stockage des analyses en base de données
- Affichage dans le dashboard admin

## 🛠️ Stack Technologique

| Composant | Technologie |
|-----------|-------------|
| **Backend** | Laravel 12 |
| **Frontend** | Angular |
| **Admin Panel** | Laravel Filament |
| **Base de données** | PostgreSQL |
| **Containerisation** | Docker |
| **IA** | ChatGPT API (OpenAI) |
| **Authentification** | Email/mot de passe |

## 🐳 Installation et Démarrage

### Prérequis
- Docker et Docker Compose
- Node.js 18+ (pour le frontend)
- PHP 8.2+ (pour le backend)
- Composer

### 🚀 Démarrage Rapide avec Docker
```bash
# Démarrer l'ensemble de la stack
./start-full-stack.sh

# Ou utiliser docker-compose directement
docker-compose -f docker-compose.full-stack.yml up -d
```

### 💻 Développement Local

#### Backend (Laravel)
```bash
cd example-app
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

#### Frontend (Angular)
```bash
cd example-app-frontend
npm install
ng serve
```

## 📂 Structure Détaillée des Dossiers

```
stage_été/
├── 🔧 example-app/                    # Backend Laravel
│   ├── app/                           # Code application Laravel
│   │   ├── Http/Controllers/          # Contrôleurs API
│   │   ├── Models/                    # Modèles Eloquent
│   │   ├── Services/                  # Services métier
│   │   └── Filament/                  # Admin panel
│   ├── config/                        # Configuration Laravel
│   ├── database/                      # Migrations et seeders
│   │   ├── migrations/                # Schéma base de données
│   │   └── seeders/                   # Données de test
│   ├── public/                        # Point d'entrée web (index.php)
│   ├── resources/                     # Vues et assets
│   ├── routes/                        # Routes API et web
│   │   ├── api.php                    # Routes API
│   │   └── web.php                    # Routes web
│   └── storage/                       # Stockage fichiers
│
├── 🎨 example-app-frontend/           # Frontend Angular
│   ├── src/                           # Code source Angular
│   │   ├── app/                       # Composants et services
│   │   │   ├── components/            # Composants UI
│   │   │   ├── services/              # Services Angular
│   │   │   ├── models/                # Modèles TypeScript
│   │   │   └── interfaces/            # Interfaces TypeScript
│   │   ├── environments/              # Configuration environnements
│   │   └── assets/                    # Assets statiques
│   └── public/                        # Fichiers publics
│
├── 🌐 nginx/                          # Configuration Nginx
├── 🐳 docker-compose.full-stack.yml   # Configuration Docker
└── 📚 README.md                       # Documentation
```

## 🔌 API Endpoints

### 🔐 Authentification
- `POST /api/login` - Connexion utilisateur
- `POST /api/register` - Inscription utilisateur
- `POST /api/logout` - Déconnexion
- `POST /api/forgot-password` - Mot de passe oublié

### 📋 Gestion des CV
- `POST /api/candidates` - Soumission d'un nouveau CV
- `GET /api/candidates` - Liste des candidats (admin uniquement)
- `GET /api/candidates/{id}` - Détails d'un candidat
- `POST /api/analyze-cv` - Déclencher l'analyse IA d'un CV
- `GET /api/cv-analysis/{id}` - Récupérer l'analyse d'un CV

## 🔄 Workflow de Développement

### Git Workflow
1. **Planifier** les tâches dans GitHub Projects ou Trello
2. **Créer** une branche feature : `git checkout -b feature/nom-feature`
3. **Développer** et commiter régulièrement
4. **Pousser** et créer une Pull Request
5. **Review** et merge après validation
6. **Démo** hebdomadaire des progrès

### 🧪 Tests
```bash
# Backend (Laravel)
cd example-app
php artisan test

# Frontend (Angular)
cd example-app-frontend
npm test
npm run e2e
```

## 🚀 Déploiement

### Production avec Docker
```bash
# Construire et démarrer en production
docker-compose -f docker-compose.full-stack.yml up -d --build

# Vérifier les logs
docker-compose -f docker-compose.full-stack.yml logs -f
```

### Variables d'Environnement
Configurer les variables suivantes :
- `OPENAI_API_KEY` - Clé API ChatGPT
- `DB_CONNECTION` - Configuration PostgreSQL
- `MAIL_*` - Configuration email
- `APP_URL` - URL de l'application

## 🎯 Livrables du Projet

- ✅ Plateforme fonctionnelle (setup Docker local)
- ✅ API REST fonctionnelle avec intégration IA
- ✅ Formulaire Angular pour soumission CV
- ✅ Dashboard Filament pour les admins
- ✅ Code propre et documenté dans Git
- ✅ Rapport final de projet ou README complet

## 🤝 Contribution

1. **Fork** le projet
2. **Créer** une branche feature (`git checkout -b feature/AmazingFeature`)
3. **Commiter** les changements (`git commit -m 'Add some AmazingFeature'`)
4. **Pousser** vers la branche (`git push origin feature/AmazingFeature`)
5. **Ouvrir** une Pull Request

## 📞 Support

Pour toute question ou problème, créer une issue dans le repository GitHub.

---

**Développé dans le cadre du stage d'été - Système de Filtrage de CV avec IA** 🎓
