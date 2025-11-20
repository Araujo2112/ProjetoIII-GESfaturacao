# Stage 1: Build Node assets
FROM node:20-slim AS node-build

WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build


# Stage 2: Build PHP with Composer
FROM php:8.2-cli AS php-build

RUN apt-get update && apt-get install -y curl unzip zip git

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY --from=node-build /app /app

RUN composer install --no-dev --optimize-autoloader


# Stage 3: Production image with PHP-FPM and nginx
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y nginx zip unzip git supervisor

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

# Copy app and built assets from php-build stage
COPY --from=php-build /app /var/www/html

# Configure nginx and supervisor as no-change (igual ao seu atual)

# (Inclua aqui seu código nginx e supervisord)

EXPOSE 80

CMD ["/usr/bin/supervisord"]
