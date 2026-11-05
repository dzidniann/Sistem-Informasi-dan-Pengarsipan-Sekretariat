FROM php:8.2.35-fpm

COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Install dependency sistem yang dibutuhkan extension PHP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    qpdf \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install ekstensi PHP yang dipakai Laravel (termasuk GD untuk proses gambar/foto notulensi)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip curl intl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set direktori kerja di dalam container
WORKDIR /var/www/html

# Salin source code project ke dalam image
COPY . .

# Install dependency PHP (composer.json harus sudah ada di context build)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set kepemilikan folder storage dan bootstrap/cache agar bisa ditulis Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
