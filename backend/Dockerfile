FROM dunglas/frankenphp:php8.3-alpine

ENV PHP_OPCACHE_ENABLE=1
ENV FRANKENPHP_CONFIG="worker ./public/frankenphp-worker.php"

# install packages
RUN apk add --no-cache \
    bash \
    curl \
    git \
    supervisor \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN install-php-extensions \
    intl \
    pdo_pgsql \
    pcntl \
    redis \
    zip \
    gd \
    bcmath

COPY . /app
WORKDIR /app

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-dev \
    --optimize-autoloader \
    --prefer-dist

RUN chmod -R 755 storage \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 bootstrap/cache \
    && mkdir -p /app/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer \
    && chmod -R 775 /app/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer \
    && php artisan octane:install --server=frankenphp

EXPOSE 80 443 443/udp

CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=80"]
