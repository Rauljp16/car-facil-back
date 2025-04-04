FROM php:8.2-fpm

# Instalar extensiones de PHP necesarias
RUN apt-get update && apt-get install -y \
    nginx \
    libzip-dev zip unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Copiar los archivos de Laravel al contenedor
WORKDIR /var/www
COPY . .

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install

# Copiar configuración personalizada de Nginx
COPY nginx.conf /etc/nginx/nginx.conf

# Exponer el puerto
EXPOSE 80

# Comando para iniciar PHP y Nginx
CMD service nginx start && php-fpm
