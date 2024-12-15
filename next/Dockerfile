# Utiliser l'image Node.js officielle comme base
FROM node:18-alpine

# Définir le répertoire de travail dans le conteneur
WORKDIR /app

# Copier les fichiers package.json et package-lock.json
COPY next/package*.json ./

# Installer les dépendances
RUN npm install

# Copier le reste des fichiers de l'application
COPY next/ .

# Exposer le port 3000 (port par défaut de Next.js)
EXPOSE 3000

# Démarrer l'application en mode développement
CMD ["npm", "run", "dev"]