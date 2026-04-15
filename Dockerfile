FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    fcgi \
    libpq \
    libpq-dev \
    postgresql-client \
    shadow \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

WORKDIR /var/www/html

COPY . /var/www/html
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && chown -R www-data:www-data /var/www/html

EXPOSE 9000
