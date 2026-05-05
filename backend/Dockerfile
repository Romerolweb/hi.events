# syntax=docker/dockerfile:1.6
# Multi-stage build for Hi.Events backend on FrankenPHP + Laravel Octane.

# ---- Composer binary stage ----
FROM composer:2 AS composer-bin

# ---- Base PHP image with extensions + security patches ----
FROM dunglas/frankenphp:1-php8.3-alpine AS base

ENV SERVER_NAME=":8000"
ENV LARAVEL_OCTANE=1

RUN apk upgrade --no-cache \
 && apk add --no-cache curl \
 && install-php-extensions \
        gd redis pdo_pgsql sodium intl mbstring xml \
        zip bcmath pcntl imagick opcache

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini

WORKDIR /app

# ---- Vendor stage: composer install with maximum layer cache ----
FROM base AS vendor

COPY --from=composer-bin /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install \
        --no-interaction --no-dev \
        --no-scripts --no-autoloader \
        --prefer-dist

# ---- Final runtime image ----
FROM base AS final

COPY --from=composer-bin /usr/bin/composer /usr/local/bin/composer
COPY --from=vendor /app/vendor ./vendor
COPY . /app

RUN composer dump-autoload --optimize --no-dev \
 && mkdir -p storage bootstrap/cache \
        /app/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer \
 && chmod -R 775 storage bootstrap/cache \
        /app/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer \
 && chown -R www-data:www-data storage bootstrap/cache \
        /app/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer

USER www-data

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -fsS http://localhost:8000/api/health || exit 1

CMD ["php", "artisan", "octane:start", \
     "--server=frankenphp", "--host=0.0.0.0", "--port=8000", \
     "--workers=auto", "--max-requests=500"]
