# Use Alpine-based PHP image
FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install system dependencies with correct Alpine package names
RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    libzip-dev \
    supervisor \
    nginx \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    shadow \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install \
    pdo_pgsql \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    sockets

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Create nginx directories and copy configs
RUN mkdir -p /run/nginx /var/log/supervisor
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Fix permissions for macOS compatibility
RUN usermod -u 1000 www-data && \
    groupmod -g 1000 www-data && \
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Generate application key if not exists
RUN if [ ! -f .env ]; then \
        cp .env.example .env && php artisan key:generate; \
    fi

# Expose ports
EXPOSE 9000 80

# Start supervisor to manage processes
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]