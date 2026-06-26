# Menggunakan base image PHP 8.2 dengan Apache
FROM php:8.2-apache

# Menginstal ekstensi PDO MySQL dan sockets (dibutuhkan oleh php-amqplib)
RUN apt-get update && apt-get install -y git zip unzip \
    && docker-php-ext-install pdo pdo_mysql sockets

# Mengaktifkan mod_rewrite Apache untuk routing front-controller
RUN a2enmod rewrite

# Menyalin composer dari image resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Mengarahkan document root Apache ke folder public/ (front controller pattern)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Menyalin seluruh kode source PHP ke dalam container
COPY . /var/www/html/
WORKDIR /var/www/html

# Memberikan hak akses folder kepada Apache
RUN chown -R www-data:www-data /var/www/html

RUN composer install --no-interaction --optimize-autoloader

# Membuka port 80 (di docker-compose.yml ini dipetakan ke 8000)
EXPOSE 80
