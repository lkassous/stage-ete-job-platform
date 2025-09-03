-- Script d'initialisation pour PostgreSQL
-- Ce fichier sera exécuté automatiquement lors de la création du conteneur

-- Créer des extensions utiles
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Vous pouvez ajouter d'autres commandes SQL d'initialisation ici
-- Par exemple, créer des tables supplémentaires, des utilisateurs, etc.
