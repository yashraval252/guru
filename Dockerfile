FROM php:7.4-apache

RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    unzip \
    curl \
    && docker-php-ext-install pdo_mysql mbstring zip

# Enable apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader

# Set permissions for sessions and cache if needed
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
