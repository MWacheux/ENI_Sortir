FROM php:8.3-apache

# Dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libicu-dev libzip-dev zlib1g-dev \
    && pecl install grpc \
    && docker-php-ext-enable grpc \
    && docker-php-ext-install pdo pdo_pgsql intl zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Apache mod_rewrite
RUN a2enmod rewrite
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier uniquement les fichiers nécessaires au composer install
COPY composer.json composer.lock ./

# Installer les dépendances en cacheant le vendor
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# Ensuite seulement, copier le reste du code
COPY . .

# Permissions
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var vendor \
    && chmod -R 775 var vendor

ENV APP_ENV=preprod

EXPOSE 80

CMD ["sh", "-c", "composer run-script post-install-cmd && apache2-foreground"]
