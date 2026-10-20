# syntax=docker/dockerfile:1

# Marguerite's Athenaeum: one image with PHP-FPM + Nginx serving Laravel.
# Built in three stages so the final image has no Composer cache, Node or
# node_modules in it.

ARG PHP_IMAGE=serversideup/php:8.4-fpm-nginx

# 1. PHP dependencies ---------------------------------------------------------
FROM ${PHP_IMAGE} AS vendor
USER root
WORKDIR /var/www/html

# Install packages first, so this layer is reused until composer.lock changes
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev

# 2. Front-end assets ---------------------------------------------------------
# Wayfinder runs `php artisan` while Vite builds, so this stage needs PHP
# (from stage 1) and Node (copied in from the official image).
FROM vendor AS assets
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

# VITE_* values are baked into the JavaScript at build time, and .env is
# kept out of the image, so public ones are set here
ENV VITE_APP_NAME="Marguerite's Athenaeum"

RUN npm ci && npm run build

# 3. Runtime ------------------------------------------------------------------
FROM ${PHP_IMAGE} AS app

# serversideup/php reads these at start-up:
# - AUTORUN runs migrations, storage:link and the config/route/view caches
# - the opcache keeps compiled PHP in memory
ENV AUTORUN_ENABLED=true \
    PHP_OPCACHE_ENABLE=1

COPY --chown=www-data:www-data --from=vendor /var/www/html /var/www/html
COPY --chown=www-data:www-data --from=assets /var/www/html/public/build /var/www/html/public/build

# Nginx in this image listens on 8080 as the unprivileged www-data user
EXPOSE 8080
