FROM php:8.3-fpm-alpine
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql mysqli redis
ADD php.ini /usr/local/etc/php/conf.d/ec.ini