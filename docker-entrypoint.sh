#!/bin/sh
set -e

# Installer les dépendances si elles ne sont pas déjà installées
if [ ! -d "vendor" ]; then
    composer install --no-dev --optimize-autoloader
fi

# Exécuter la commande passée en argument
exec "$@"