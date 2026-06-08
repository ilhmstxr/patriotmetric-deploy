FROM php:8.3-alpine

# 1. Install system dependencies & Node.js (Vite 7 membutuhkan Node v18+)
RUN apk add --no-cache \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    nodejs \
    npm \
    git \
    unzip \
    bash

# 2. Install & konfigurasi PHP Extensions yang diwajibkan oleh Filament v4 & Intervention Image
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql intl gd zip exif

# 3. Ambil Composer versi terbaru dari image resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Tentukan working directory aplikasi
WORKDIR /var/www

# 5. Salin file manifest untuk memanfaatkan layer caching Docker
COPY composer.json package.json ./

# 6. Jalankan instalasi dependensi
RUN composer install --no-scripts --no-autoloader
RUN npm install

# 7. Salin seluruh source code lokal ke dalam container
COPY . .

# 8. Selesaikan proses autoloading Composer
RUN composer dump-autoload

# 9. Expose port 8000 (Laravel) dan port 5173 (Vite Server HMR)
EXPOSE 8000 5173

# 10. Jalankan entrypoint default
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]