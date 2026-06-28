FROM php:8.3.31-alpine3.23

# 1. Install system dependencies & Node.js (Vite 7 membutuhkan Node v18+)
RUN apk add --no-cache \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    git \
    unzip \
    bash

# Copy Node.js and NPM strictly from official Node image
COPY --from=node:24.14.1-alpine3.23 /usr/local/bin/node /usr/local/bin/node
COPY --from=node:24.14.1-alpine3.23 /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# 2. Install & konfigurasi PHP Extensions yang diwajibkan oleh Filament v4 & Intervention Image
RUN apk add --no-cache libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql intl gd zip exif

# 3. Ambil Composer versi terbaru dari image resmi
COPY --from=composer:2.10.1 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_HTTP_VERSION=1.1

# 4. Tentukan working directory aplikasi
WORKDIR /var/www

# 5. Salin file manifest untuk memanfaatkan layer caching Docker
COPY composer.json package.json package-lock.json ./

# 6. Jalankan instalasi dependensi
RUN composer install --no-scripts --no-autoloader
RUN npm install --ignore-scripts

# 7. Salin seluruh source code lokal ke dalam container
COPY . .

# 8. Selesaikan proses autoloading Composer
RUN composer dump-autoload

# 9. Expose port 8000 (Laravel) dan port 5173 (Vite Server HMR)
EXPOSE 8000 5173

# 10. Jalankan entrypoint default
# CMD ["composer", "run", "dev", "--host=0.0.0.0", "--port=8000"]    
CMD ["composer", "run", "dev"]    