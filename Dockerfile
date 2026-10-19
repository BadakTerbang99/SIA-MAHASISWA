FROM php:8.2-apache

# Install SQLite dependencies & extensions
RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Copy app files
COPY . /var/www/html/

# Configure permissions for SQLite database and uploads
RUN mkdir -p /var/www/html/database /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/database /var/www/html/uploads \
    && chmod -R 775 /var/www/html/database /var/www/html/uploads

EXPOSE 80
CMD ["apache2-foreground"]
