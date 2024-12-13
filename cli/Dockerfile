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

WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . .

# Installer les dépendances avec Composer
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

# Vérifier que le dossier vendor existe
RUN ls -la /var/www/html/vendor

# Définir la commande par défaut
# CMD ["php", "-a"]

RUN chown -R www-data:www-data /var/www/html/database
RUN chmod -R 777 /var/www/html/database
RUN chown -R www-data:www-data /var/www/html/vendor