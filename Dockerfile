FROM php:8.1-fpm-alpine

RUN docker-php-ext-install pdo_mysql

RUN curl -sS https://getcomposer.org/installer​| php -- \
     --install-dir=/usr/local/bin --filename=composer

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install
EXPOSE 8050
CMD php artisan serve --host=0.0.0.0 --port=8050
