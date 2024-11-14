FROM php:8.0-cli

# Installer les dépendances nécessaires
RUN apt-get clean && rm -rf /var/lib/apt/lists/* \
    && apt-get update -y \
    && apt-get install -y \
        sqlite3 \
        libsqlite3-dev \
        libzip-dev \
        unzip \
        git \
    && docker-php-ext-install pdo pdo_sqlite zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer les dossiers nécessaires
RUN mkdir -p /var/www/html/services \
    && mkdir -p /var/www/html/database

WORKDIR /var/www/html

# Script d'entrée pour installer les dépendances au démarrage
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]