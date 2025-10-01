FROM php:8.3-apache

# Dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libicu-dev libzip-dev zlib1g-dev \
    && docker-php-ext-install pdo pdo_pgsql intl zip opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache rewrite
RUN a2enmod rewrite

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copier tout le code en même temps (inclut bin/console)
COPY . /var/www/html

# Forcer environnement prod
#ENV APP_ENV=preprod

# Installer les dépendances Composer après avoir tout copié
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts
#RUN composer install --optimize-autoloader --no-interaction --no-progress

# Permissions (ajuste si besoin)
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log \
    && chown -R www-data:www-data /var/www/html/var /var/www/html/vendor \
    && chmod -R 775 /var/www/html/var /var/www/html/vendor

EXPOSE 80
CMD ["sh", "-c", "apache2-foreground"]
