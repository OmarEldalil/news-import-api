FROM php:8.4

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy custom PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www

COPY . /var/www

RUN composer install

COPY .env.docker .env

RUN touch /var/www/storage/database.sqlite

RUN php artisan migrate

RUN php artisan storage:link

# Ensure storage directory is writable
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
