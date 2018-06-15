FROM php:7.0-apache

RUN apt-get update && \
    apt-get install curl && \
    curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

COPY . /var/www/html

RUN composer install

COPY vhost.conf /etc/apache2/sites-enabled/praatmee.conf

RUN chown -R www-data:www-data /var/www/html
