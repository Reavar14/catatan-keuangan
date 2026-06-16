FROM php:8.2-cli

# Instalasi dependensi
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libicu-dev libzip-dev libpq-dev zip unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql pdo_pgsql gd intl zip

# Instal Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Tentukan folder kerja
WORKDIR /var/www/html

# Salin isi folder backend ke container
COPY backend/ .

# Jalankan composer install
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader

# PERBAIKAN: Gunakan perintah 'mkdir' agar folder selalu ada sebelum 'chown'
RUN mkdir -p storage bootstrap/cache && \
    chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]