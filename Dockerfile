FROM php:8.2-cli

# Update dan instal semua dependensi yang diperlukan sekaligus
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql pdo_pgsql gd intl zip

# Tentukan folder kerja
WORKDIR /var/www/html

# Izinkan Composer berjalan sebagai root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Salin semua file
COPY . .

# Salin Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Jalankan instalasi composer
RUN composer install --no-dev --optimize-autoloader

# Atur permission
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]