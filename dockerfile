FROM php:8.2-fpm

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    nginx \
    libzip-dev zip unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear directorio de trabajo
WORKDIR /var/www

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copiar configuración personalizada de Nginx
COPY nginx.conf /etc/nginx/nginx.conf

# Railway usa el puerto 8080 por defecto
ENV PORT=8080

# Cambiar el puerto de escucha de nginx
RUN sed -i 's/listen 80;/listen 8080;/' /etc/nginx/nginx.conf

# Expone el puerto 8080
EXPOSE 8080

# Usar supervisord para correr nginx y php-fpm en primer plano
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
