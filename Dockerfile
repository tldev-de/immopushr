FROM php:8-alpine

# install php extensions
RUN apk add --no-cache zlib-dev libzip-dev && docker-php-ext-install zip

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# copy application
COPY . /app

# specify volumes
VOLUME /app/db

ENTRYPOINT /app/entrypoint.sh