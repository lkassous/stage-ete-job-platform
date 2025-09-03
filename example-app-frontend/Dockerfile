# Multi-stage build pour optimiser la taille de l'image

# Stage 1: Build de l'application Angular
FROM node:20-alpine AS builder

# Définir le répertoire de travail
WORKDIR /app

# Copier les fichiers de configuration des dépendances
COPY package*.json ./

# Installer les dépendances (toutes les dépendances pour le build)
RUN npm ci

# Copier le code source
COPY . .

# Build de l'application pour le développement (plus permissif)
RUN npm run build

# Stage 2: Serveur Nginx pour servir l'application
FROM nginx:alpine

# Copier la configuration Nginx personnalisée
COPY nginx.conf /etc/nginx/nginx.conf

# Copier les fichiers buildés depuis le stage précédent
COPY --from=builder /app/dist/example-app-frontend/browser /usr/share/nginx/html

# Renommer index.csr.html en index.html pour Nginx
RUN if [ -f /usr/share/nginx/html/index.csr.html ]; then \
        mv /usr/share/nginx/html/index.csr.html /usr/share/nginx/html/index.html; \
    fi

# Exposer le port 80
EXPOSE 80

# Commande par défaut
CMD ["nginx", "-g", "daemon off;"]
