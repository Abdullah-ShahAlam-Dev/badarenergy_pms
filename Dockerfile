# Stage 1: Build assets
FROM node:18-alpine as assets

WORKDIR /app

# Copy package files and install dependencies
COPY package*.json ./
RUN npm ci

# Copy the rest of the application for assets build
COPY . .

# Build assets (Mix)
RUN npm run prod

# Stage 2: Production PHP environment
FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zlib-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    linux-headers \
    git \
    unzip \
    nginx \
    supervisor \
    mysql-client

# Hijack the mysql command to force-disable SSL during migrations
RUN mkdir -p /etc/mysql/conf.d \
    && echo -e "[client]\nssl=0" > /etc/my.cnf.d/client.cnf \
    && mv /usr/bin/mysql /usr/bin/mysql.real \
    && echo -e '#!/bin/sh\n/usr/bin/mysql.real --ssl=0 "$@"' > /usr/bin/mysql \
    && chmod +x /usr/bin/mysql

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        mysqli \
        pdo_mysql \
        mbstring \
        zip \
        opcache \
        intl \
        exif \
        pcntl

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Copy built assets from Stage 1
COPY --from=assets /app/public/mix-manifest.json ./public/
COPY --from=assets /app/public/css ./public/css
COPY --from=assets /app/public/js ./public/js

# Wipe existing cache from host to prevent class-not-found errors during build
RUN rm -rf bootstrap/cache/*.php

# Ensure required directories exist and have correct permissions
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Optimize Laravel for production
RUN composer install --no-dev --optimize-autoloader --no-scripts
RUN php artisan config:clear && php artisan cache:clear

# Final permissions check
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 9000
EXPOSE 9000

CMD ["php-fpm"]
