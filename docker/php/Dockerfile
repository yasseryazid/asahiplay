FROM php:8.3-fpm-alpine

RUN apk add --no-cache git unzip rsync icu-dev libzip-dev oniguruma-dev libxml2-dev curl-dev \
    && docker-php-ext-install pdo_mysql mbstring intl zip bcmath xml \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
