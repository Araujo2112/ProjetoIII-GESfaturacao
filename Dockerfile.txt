# ============================
# Build Stage (Node + Composer)
# ============================
FROM node:20-slim AS build-stage

# Install dependencies needed for PHP composer
RUN apt-get update && apt-get install -y curl zip unzip git

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build Vite assets
RUN npm install
RUN npm run build


# ============================
# Production Stage (PHP)
# ============================
FROM php:8.2-fpm

# System packages
RUN apt-get update && apt-get install -y nginx zip unzip git

# Enable Laravel required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

# Copy built files from Stage 1
COPY --from=build-stage /app ./

# Copy default nginx config
RUN rm -rf /etc/nginx/sites-enabled/default
RUN printf "server {\n\
    listen 80;\n\
    server_name _;\n\
    root /var/www/html/public;\n\
\n\
    index index.php;\n\
\n\
    location / {\n\
        try_files \$uri /index.php?\$query_string;\n\
    }\n\
\n\
    location ~ \\.php$ {\n\
        include snippets/fastcgi-php.conf;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
    }\n\
}\n" > /etc/nginx/sites-enabled/laravel.conf

# Supervisor to run nginx + php-fpm
RUN apt-get install -y supervisor
RUN printf "[supervisord]\nnodaemon=true\n\
[program:php-fpm]\ncommand=/usr/local/sbin/php-fpm\n\
[program:nginx]\ncommand=/usr/sbin/nginx -g 'daemon off;'\n" > /etc/supervisor/conf.d/supervisord.conf

# Expose port Render uses
EXPOSE 80

# Start services
CMD ["/usr/bin/supervisord"]
