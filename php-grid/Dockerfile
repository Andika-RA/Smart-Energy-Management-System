# Menggunakan base image PHP 8.2 dengan Apache
FROM php:8.2-apache

# Menginstal ekstensi PDO MySQL yang diwajibkan untuk koneksi database
RUN apt-get update && apt-get install -y git zip unzip \
    && docker-php-ext-install pdo pdo_mysql

# Mengaktifkan mod_rewrite Apache untuk routing MVC
RUN a2enmod rewrite

# Menyalin composer dari image resmi (dibutuhkan karena service ini kini memakai
# Composer PSR-4 autoload, seragam dengan citizen-service & power-service)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# BUG FIX: mengarahkan document root Apache ke folder public/, karena index.php
# berada di public/index.php, bukan di /var/www/html langsung. Tanpa baris ini,
# Apache tidak pernah menemukan file untuk dieksekusi.
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Mengatur direktori kerja
WORKDIR /var/www/html

# Menyalin seluruh kode source PHP ke dalam container
COPY . .

RUN composer install --no-interaction --optimize-autoloader

# Memberikan hak akses folder kepada Apache
RUN chown -R www-data:www-data /var/www/html

# Membuka port 80 (di docker-compose.yml ini di-mapping ke 8002)
EXPOSE 80
