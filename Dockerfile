# Use official PHP image with Apache
FROM php:7.4-apache

# Enable Apache mod_rewrite for CodeIgniter
RUN a2enmod rewrite

# Copy all project files to the Apache web root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Set permissions (for Linux-based web hosting)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Allow .htaccess overrides for mod_rewrite
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Expose default Apache port
EXPOSE 80
