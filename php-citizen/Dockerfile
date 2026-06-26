# Menggunakan base image PHP 8.2 dengan Apache
FROM php:8.2-apache

# Menginstal ekstensi PDO MySQL yang diwajibkan untuk koneksi database
RUN docker-php-ext-install pdo pdo_mysql

# Mengaktifkan mod_rewrite Apache untuk routing MVC
RUN a2enmod rewrite

# Mengatur direktori kerja
WORKDIR /var/www/html

# Menyalin seluruh kode source PHP ke dalam container
COPY . .

# Memberikan hak akses folder kepada Apache
RUN chown -R www-data:www-data /var/www/html

# Membuka port 80 (Di docker-compose.yml nanti ini yang di-mapping ke 8000, 8001, 8002)
EXPOSE 80