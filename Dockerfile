FROM php:8.1-apache

# Install SQLite3 and required PHP extensions
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules for URL rewriting and security headers
RUN a2enmod rewrite headers

# Copy Apache virtual host configuration
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Note: Application files will be mounted via docker compose

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
