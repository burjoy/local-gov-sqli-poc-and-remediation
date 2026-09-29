FROM php:8.1-apache

# SQLite support for the lab database
RUN docker-php-ext-install pdo_sqlite \
    && a2enmod rewrite

# Serve the vulnerable app from the docroot
COPY . /var/www/html/

# Writable location for the generated SQLite database
RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
