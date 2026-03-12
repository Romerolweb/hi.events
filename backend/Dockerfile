FROM dunglas/frankenphp:php8.3-alpine

ENV SERVER_NAME=":80"

# Install dependencies and extensions
RUN install-php-extensions \
    gd \
    pdo_pgsql \
    sodium \
    curl \
    intl \
    mbstring \
    xml \
    zip \
    bcmath \
    pcntl \
    imagick \
    opcache

WORKDIR /app

COPY . /app

RUN chmod -R 755 storage \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 bootstrap/cache \
    && chown -R root:root storage bootstrap/cache

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-dev \
    --optimize-autoloader \
    --prefer-dist

RUN mkdir -p /app/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer \
    && chmod -R 775 /app/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer \
    && chown -R root:root /app/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer

EXPOSE 8000

CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000", "--workers=auto"]
