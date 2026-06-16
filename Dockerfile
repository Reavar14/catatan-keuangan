FROM php:8.2-cli

# Instal dependensi (ini sudah benar)
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libicu-dev libzip-dev libpq-dev zip unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql pdo_pgsql gd intl zip

# Tentukan folder kerja SESUAI dengan lokasi composer.json Anda
# Jika file composer.json ada di folder 'backend', maka gunakan:
WORKDIR /var/www/html/backend

# Izinkan superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy semua file ke dalam container
COPY . .

# Instal composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Jalankan install
RUN composer install --no-dev --optimize-autoloader

# Atur permission
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]