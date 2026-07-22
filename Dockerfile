# Stage 1: Build assets
FROM node:20-alpine AS assets-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Runtime
# Uses Alpine native PHP83 packages - all pre-compiled binaries, no source compilation!
FROM alpine:3.21

# Install nginx, supervisor, PHP 8.3 + all extensions as pre-compiled Alpine packages (~30 seconds)
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    unzip \
    php83 \
    php83-fpm \
    php83-pdo \
    php83-pdo_mysql \
    php83-mbstring \
    php83-gd \
    php83-zip \
    php83-intl \
    php83-bcmath \
    php83-exif \
    php83-pcntl \
    php83-opcache \
    php83-xml \
    php83-tokenizer \
    php83-ctype \
    php83-curl \
    php83-fileinfo \
    php83-session \
    php83-dom \
    php83-openssl \
    php83-simplexml \
    php83-xmlwriter \
    php83-xmlreader \
    php83-phar \
    php83-iconv \
    php83-sodium

# Create /usr/bin/php symlink so artisan & composer work
RUN ln -sf /usr/bin/php83 /usr/bin/php

# Configure PHP opcache for production
RUN echo "opcache.enable=1" >> /etc/php83/php.ini && \
    echo "opcache.memory_consumption=128" >> /etc/php83/php.ini && \
    echo "opcache.max_accelerated_files=10000" >> /etc/php83/php.ini && \
    echo "opcache.validate_timestamps=0" >> /etc/php83/php.ini

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project files with correct ownership from the start (faster than chown -R after)
COPY --chown=nginx:nginx . .
COPY --chown=nginx:nginx --from=assets-builder /app/public/build ./public/build

# Copy nginx & supervisor configs
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php-fpm-www.conf /etc/php83/php-fpm.d/www.conf

# Install PHP dependencies first (vendor/autoload.php needed before artisan runs)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Generate app key using a dummy .env
RUN cp .env.example .env && php artisan key:generate

# Only chmod the directories that need write access
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
