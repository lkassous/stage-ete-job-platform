# Utiliser l'image Nginx officielle
FROM nginx:alpine

# Copier la configuration Nginx personnalisée
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Créer les répertoires nécessaires
RUN mkdir -p /var/log/nginx

# Exposer le port 80
EXPOSE 80

# Commande par défaut
CMD ["nginx", "-g", "daemon off;"]
