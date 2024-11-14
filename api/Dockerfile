FROM php:8.0-apache

# Copier les fichiers de l'API
COPY . /var/www/html/

# Copier la configuration Apache
COPY config/apache.conf /etc/apache2/conf-available/apache.conf
RUN a2enconf apache

# Exposer le port 80
EXPOSE 80

# Commande par défaut pour démarrer Apache
CMD ["apache2-foreground"]