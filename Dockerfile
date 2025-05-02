FROM php:8.2-apache

# Copia el contenido del proyecto al contenedor
COPY . /var/www/html/

# Habilita módulos necesarios (por ejemplo, mysqli)
RUN docker-php-ext-install mysqli

# Exponer el puerto 80
EXPOSE 80
