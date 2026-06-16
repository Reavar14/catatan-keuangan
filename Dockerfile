FROM php:8.2-cli

# 1. Instal dependensi
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libicu-dev libzip-dev zip unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_pgsql gd intl zip

# 2. Tentukan folder kerja (Sesuaikan jika composer.json Anda di dalam subfolder)
WORKDIR /var/www/html

# 3. Izinkan superuser AGAR TIDAK ERROR "Do not run Composer as root"
ENV COMPOSER_ALLOW_SUPERUSER=1

# 4. Salin file project DULU, baru jalankan composer install
COPY . .

# 5. Salin composer dari image resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Jalankan instalasi
RUN composer install --no-dev --optimize-autoloader

# 7. Sisa konfigurasi
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
EXPOSE 80
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]