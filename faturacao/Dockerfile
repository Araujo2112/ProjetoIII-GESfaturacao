# PHP + Apache
FROM php:8.2-apache

# Dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

# Apache rewrite (Laravel)
RUN a2enmod rewrite

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Diretório da app
WORKDIR /var/www/html

# Copiar código
COPY . .

# Permissões
RUN chown -R www-data:www-data storage bootstrap/cache

# Instalar dependências
RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

# Apache -> public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

EXPOSE 80

CMD ["apache2-foreground"]
