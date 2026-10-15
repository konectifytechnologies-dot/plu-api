
# Set the base image
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    vim \
    unzip \
    zip \
    curl \
    curl-dev \
    zlib-dev \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Switch to www-data
USER www-data

# Install Composer dependencies
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# Expose Laravel port
EXPOSE 7070

# Start Laravel development server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=7070"]
