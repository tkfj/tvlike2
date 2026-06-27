FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN touch /var/www/database/database.sqlite 
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database 
RUN pwd && ls -la && composer --version
RUN composer install --no-dev --optimize-autoloader --no-scripts --prefer-source
# RUN php artisan optimize 
RUN : > .env

VOLUME /var/www/public

EXPOSE 9000
CMD ["php-fpm"]



