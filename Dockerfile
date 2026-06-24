FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN rm -rf bootstrap/cache/*
RUN composer install --no-dev --optimize-autoloader --no-scripts
RUN php artisan optimize

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database

VOLUME /var/www/public

## php-fpmのマスターはrootで動かすのでUSERは指定しない。(ワーカーはwww-dataで動く)
# USER www-data

EXPOSE 9000
CMD ["php-fpm"]



