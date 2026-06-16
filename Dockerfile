FROM php:8.2-cli

# 1. Instal dependensi
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libicu-dev libzip-dev libpq-dev zip unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql pdo_pgsql gd intl zip

# 2. Set WORKDIR ke root saja
WORKDIR /var/www/html

# 3. Izinkan superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# 4. Copy SEMUA file dari komputer Anda ke dalam container
COPY . .

# 5. Salin Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Jalankan perintah untuk mencari composer.json secara dinamis
# Perintah ini akan masuk ke folder manapun yang berisi composer.json dan menjalankannya
RUN find . -name "composer.json" -exec dirname {} \; | xargs -I {} composer install --working-dir={} --no-dev --optimize-autoloader

# 7. Sisa konfigurasi
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]