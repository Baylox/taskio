# Build stage for Vite assets
FROM node:22-alpine AS assets

WORKDIR /app

# Copy Node.js configuration files
COPY package.json ./
RUN npm install

# Copy assets and build
COPY assets/ ./assets/
COPY vite.config.js ./
COPY tailwind.config.js* ./
COPY postcss.config.js* ./

RUN npm run build

# Production stage with PHP and Apache
FROM php:8.4-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy Apache configuration
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www

# Copy source code first
COPY . .

# Install dependencies after copying the code
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy built assets from previous stage
COPY --from=assets /app/public/build ./public/build

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

# Environment variables
ENV APP_ENV=dev
ENV APP_DEBUG=1

EXPOSE 80

CMD ["apache2-foreground"]
