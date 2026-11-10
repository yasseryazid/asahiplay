FROM php:8.3-fpm-alpine

RUN apk add --no-cache git unzip rsync icu-dev libzip-dev oniguruma-dev libxml2-dev curl-dev \
    && docker-php-ext-install pdo_mysql mbstring intl zip bcmath xml opcache \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/entrypoint.sh docker/php/entrypoint.prod.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/entrypoint.prod.sh

WORKDIR /var/www/html
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
