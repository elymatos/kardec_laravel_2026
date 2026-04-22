FROM framenetbrasil/php-fpm:8.5

ARG WWWGROUP=1001
ARG WWWUSER=1000
ARG PROD

RUN addgroup -g $WWWGROUP www \
    && adduser -s /usr/bin/fish -D -G www -u $WWWUSER sail \
    && mkdir /var/log/laravel \
    && touch /var/log/laravel/laravel.log \
    && chown -R sail:www /var/log/laravel \
    && apk add --no-cache graphviz ttf-freefont font-noto

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN mkdir -p /www && chown sail:www /www

COPY --chown=sail:www composer.json /www/

USER sail
WORKDIR /www

RUN if [ -n "$PROD" ]; then composer install --no-dev --optimize-autoloader --no-scripts; fi
