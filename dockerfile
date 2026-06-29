FROM php:8.3-fpm-alpine

# Install system dependencies, PHP extensions, Node.js, NPM, Nginx, and Supervisor
RUN apk add --no-cache \
    bash \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    mysql-client \
    oniguruma-dev \
    icu-dev \
    nodejs \
    npm \
    nginx \
    supervisor

# Install PHP Extensions (gd, pdo_mysql, intl, zip, exif, mbstring, bcmath, opcache, pcntl)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql intl zip exif mbstring bcmath opcache pcntl

# Set PHP memory limit for local development
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini

# Copy custom PHP config
COPY ./docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /opt/patriotmetric
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HTTP_VERSION=1.1

# --- CACHE LAYER: COMPOSER DEPENDENCIES ---
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-autoloader --no-scripts --prefer-dist

# --- CACHE LAYER: NPM DEPENDENCIES ---
COPY package.json package-lock.json ./
RUN npm install

# Copy application files with proper ownership
COPY --chown=www-data:www-data . .

# Finish autoloader optimization and build static assets
RUN composer dump-autoload --optimize \
    && npm run build

# Configure Nginx & Supervisor
COPY ./docker/nginx.conf /etc/nginx/http.d/default.conf
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Setup required directories and set proper ownership
RUN mkdir -p /var/log/supervisor /run/nginx /var/run \
    && chown -R www-data:www-data /var/log/nginx /var/lib/nginx /run/nginx /var/log/supervisor /opt/patriotmetric/storage /opt/patriotmetric/bootstrap/cache

# Set permissions for Entrypoint
COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
