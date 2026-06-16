FROM php:8.2-cli

# 1. Instal semua dependensi sistem
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libicu-dev libzip-dev libpq-dev zip unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql pdo_pgsql gd intl zip

# 2. Instal Composer secara resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Tentukan WORKDIR
WORKDIR /var/www/html

# 4. Copy composer.json dan composer.lock terlebih dahulu
# Jika composer.json ada di folder 'backend', ubah menjadi 'COPY backend/composer.* ./'
COPY composer.* ./

# 5. Izinkan superuser dan jalankan instalasi
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader

# 6. Copy sisa file proyek
COPY . .

# 7. Berikan izin akses folder
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]