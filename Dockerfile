FROM php:8.4-apache

ENV APP_ENV=dev \
    COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Met à jour et installe les dépendances nécessaires
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        sqlite3 \
        libsqlite3-dev \
    && docker-php-ext-install \
        intl \
        pdo_pgsql \
        pdo_sqlite \
        zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Set Apache document root to /public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copie des fichiers de configuration
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

COPY . .

RUN chown -R www-data:www-data var