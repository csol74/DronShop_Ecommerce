FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libzip-dev git unzip
RUN docker-php-ext-install pdo_mysql gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar el código
WORKDIR /var/www
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000
CMD php artisan serve --host=0.0.0.0 --port=8000
