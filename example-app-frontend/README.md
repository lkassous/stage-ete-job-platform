# 🎨 Frontend Angular - Système de Filtrage de CV avec IA

## 📋 Vue d'ensemble

Frontend Angular pour le système de filtrage de CV. Cette application fournit une interface utilisateur moderne et responsive pour la soumission de candidatures (CV + lettre de motivation).

## 🚀 Installation

### Prérequis
- Node.js 18+
- npm ou yarn
- Angular CLI 20+

### Configuration
```bash
# 1. Installer les dépendances
npm install

# 2. Configurer l'environnement
# Modifier src/environments/environment.ts avec l'URL de l'API Laravel

# 3. Démarrer le serveur de développement
ng serve

# L'application sera accessible sur http://localhost:4200
```

## 🏗️ Architecture

### Structure des Dossiers
```
src/
├── app/                       # Code source principal
│   ├── components/            # Composants UI
│   │   ├── cv-form/          # Formulaire de soumission CV
│   │   ├── header/           # En-tête de l'application
│   │   └── footer/           # Pied de page
│   ├── services/             # Services Angular
│   │   ├── api.service.ts    # Service API principal
│   │   ├── auth.service.ts   # Service d'authentification
│   │   └── file.service.ts   # Service de gestion des fichiers
│   ├── models/               # Modèles TypeScript
│   │   ├── candidate.model.ts # Modèle candidat
│   │   └── api-response.model.ts # Modèle réponse API
│   ├── interfaces/           # Interfaces TypeScript
│   │   └── form-data.interface.ts # Interface données formulaire
│   └── guards/               # Guards de navigation
│       └── auth.guard.ts     # Guard d'authentification
├── environments/             # Configuration environnements
│   ├── environment.ts        # Environnement développement
│   └── environment.prod.ts   # Environnement production
└── assets/                   # Assets statiques
    ├── images/               # Images
    └── styles/               # Styles globaux
```

## 🎯 Fonctionnalités

### 📤 Formulaire de Soumission CV
- **Informations personnelles** :
  - Nom (Last Name)
  - Prénom (First Name)
  - Email (avec validation)
  - Téléphone (avec format)
  - URL LinkedIn (optionnel)

- **Upload de fichiers** :
  - CV (PDF uniquement, max 5MB)
  - Lettre de motivation (PDF uniquement, max 5MB)
  - Prévisualisation des fichiers
  - Validation des types et tailles

- **Validation** :
  - Validation en temps réel
  - Messages d'erreur clairs
  - Indicateurs visuels de validation

### 🔐 Authentification (Optionnel)
- Connexion utilisateur
- Gestion des tokens
- Protection des routes

## 🛠️ Technologies Utilisées

| Composant | Technologie |
|-----------|-------------|
| **Framework** | Angular 20 |
| **Styling** | SCSS, Bootstrap/Tailwind |
| **HTTP Client** | Angular HttpClient |
| **Forms** | Reactive Forms |
| **Routing** | Angular Router |
| **State Management** | Services + RxJS |
| **File Upload** | Custom File Service |
| **Validation** | Angular Validators |

## 🔌 Intégration API

### Configuration
```typescript
// src/environments/environment.ts
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api',
  maxFileSize: 5 * 1024 * 1024, // 5MB
  allowedFileTypes: ['application/pdf']
};
```

## 🧪 Tests

### Tests Unitaires
```bash
# Exécuter tous les tests
ng test

# Tests avec couverture
ng test --code-coverage

# Tests en mode watch
ng test --watch
```

### Tests E2E
```bash
# Tests end-to-end
ng e2e

# Tests sur différents navigateurs
ng e2e --browser=chrome
ng e2e --browser=firefox
```

## 🚀 Build et Déploiement

### Build de Production
```bash
# Build optimisé pour production
ng build --configuration=production

# Build avec analyse des bundles
ng build --stats-json
npx webpack-bundle-analyzer dist/stats.json
```

### Docker
```bash
# Construire l'image Docker
docker build -t cv-system-frontend .

# Démarrer le conteneur
docker run -p 4200:80 cv-system-frontend
```

## 🔧 Configuration

### Variables d'Environnement
```typescript
// environment.prod.ts
export const environment = {
  production: true,
  apiUrl: 'https://api.cv-system.com/api',
  maxFileSize: 5 * 1024 * 1024,
  allowedFileTypes: ['application/pdf'],
  enableAnalytics: true,
  version: '1.0.0'
};
```

## 📊 Performance

### Métriques Cibles
- **First Contentful Paint** : < 1.5s
- **Largest Contentful Paint** : < 2.5s
- **Time to Interactive** : < 3.5s
- **Bundle Size** : < 500KB (gzipped)

## 🤝 Contribution

### Standards de Code
- **ESLint** : Linting du code TypeScript
- **Prettier** : Formatage automatique
- **Husky** : Git hooks pour la qualité
- **Conventional Commits** : Format des commits

### Workflow
1. Créer une branche feature
2. Développer avec tests
3. Vérifier avec `ng lint` et `ng test`
4. Créer une Pull Request

### Scripts NPM
```json
{
  "scripts": {
    "start": "ng serve",
    "build": "ng build",
    "test": "ng test",
    "lint": "ng lint",
    "e2e": "ng e2e",
    "format": "prettier --write src/**/*.{ts,html,scss}"
  }
}
```

---

**Frontend Angular pour le Système de Filtrage de CV avec IA** 🎨
