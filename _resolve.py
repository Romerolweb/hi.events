#!/usr/bin/env python3
"""Resolve merge conflicts - run once then delete this file."""
import os

BASE = os.path.dirname(os.path.abspath(__file__))

# 1. Resolve Dockerfile.all-in-one
with open(os.path.join(BASE, 'Dockerfile.all-in-one'), 'w') as f:
    f.write("""# --- Stage 1: Build the Frontend ---
FROM node:22-alpine AS node-frontend
WORKDIR /app/frontend
RUN apk add --no-cache yarn

# Increase network timeout for slow ARM emulation builds
RUN yarn config set network-timeout 600000

COPY ./frontend/package.json ./frontend/yarn.lock ./
COPY ./frontend .
COPY ./VERSION /app/VERSION
RUN yarn install --network-timeout 600000 --frozen-lockfile && yarn build

# --- Stage 2: Build the Backend with FrankenPHP ---
FROM dunglas/frankenphp:php8.3-alpine

ENV SERVER_NAME=":8000"
ENV LARAVEL_OCTANE=1
ENV APP_ENV="production"

WORKDIR /app

# Install PHP extensions
RUN set -ex \\
    && apk add --no-cache php83-pdo_pgsql php83-pgsql php83-redis php83-pcntl php83-bcmath php83-zip php83-intl php83-opcache curl supervisor \\
    && install-php-extensions gd pdo_pgsql sodium curl intl mbstring xml zip bcmath pcntl imagick opcache

# Copy Backend
COPY ./backend /app/backend
COPY ./VERSION /app/backend/VERSION

# Setup Directories and Permissions
RUN chmod -R 775 /app/backend/storage \\
    && mkdir -p /app/backend/bootstrap/cache \\
    && chmod -R 775 /app/backend/bootstrap/cache \\
    && chown -R root:root /app/backend/storage /app/backend/bootstrap/cache

# Install Composer Dependencies
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN cd /app/backend \\
    && composer install --no-interaction --no-dev --optimize-autoloader --prefer-dist

RUN mkdir -p /app/backend/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer \\
    && chmod -R 775 /app/backend/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer \\
    && chown -R root:root /app/backend/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer

# --- Stage 3: Combine Frontend, Supervisor, and Caddyfile ---
COPY --from=node-frontend /app/frontend/dist /app/frontend/dist

# Install Octane and configure FrankenPHP worker
RUN cd /app/backend && php artisan octane:install --server=frankenphp

# Setup Supervisor and Startup Scripts
COPY ./docker/all-in-one/supervisord.conf /etc/supervisord.conf
COPY ./docker/all-in-one/Caddyfile /etc/caddy/Caddyfile
COPY ./docker/all-in-one/startup.sh /startup.sh

RUN sed -i 's/\\r$//' /startup.sh && chmod +x /startup.sh

EXPOSE 8000

CMD ["/startup.sh"]
""")
print('Resolved: Dockerfile.all-in-one')

# 2. Resolve docker/development/docker-compose.dev.yml
# Keep FrankenPHP paths (/app) + add VERSION mount from main
path = os.path.join(BASE, 'docker/development/docker-compose.dev.yml')
with open(path, 'r') as f:
    content = f.read()

# Replace the conflict block with resolved version
import re
# The conflict is in the volumes section of the backend service
content = re.sub(
    r'<<<<<<< HEAD\n.*?=======\n.*?>>>>>>> main',
    """            - ./../../backend:/app
            - ./../../backend/storage:/app/storage
            - ./../../backend/bootstrap/cache:/app/bootstrap/cache
            - ./../../VERSION:/app/VERSION:ro""",
    content,
    flags=re.DOTALL
)
with open(path, 'w') as f:
    f.write(content)
print('Resolved: docker/development/docker-compose.dev.yml')

# 3. Delete the old supervisor config that main tried to keep but HEAD deleted
old_supervisor = os.path.join(BASE, 'docker/all-in-one/supervisor/supervisord.conf')
if os.path.exists(old_supervisor):
    os.remove(old_supervisor)
    supervisor_dir = os.path.join(BASE, 'docker/all-in-one/supervisor')
    if os.path.isdir(supervisor_dir) and not os.listdir(supervisor_dir):
        os.rmdir(supervisor_dir)
    print('Resolved: removed old supervisor/supervisord.conf (replaced by new supervisord.conf)')

print('All conflicts resolved!')
