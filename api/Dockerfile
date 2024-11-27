FROM php:8.0-apache

# Copier les fichiers de l'API
COPY . /var/www/html/

# Copier la configuration Apache
COPY config/apache.conf /etc/apache2/conf-available/apache.conf
RUN a2enconf apache
RUN a2enmod headers
RUN a2enmod rewrite
# Exposer le port 80
EXPOSE 80

# Commande par défaut pour démarrer Apache
CMD ["apache2-foreground"]

RUN chown -R www-data:www-data /var/www/html/database
RUN chmod -R 777 /var/www/html/database