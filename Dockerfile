FROM php:7.0-apache

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

RUN apt-get update && \
    apt-get install zip

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html

COPY vhost.conf /etc/apache2/sites-enabled/clarity.conf

USER www-data

RUN composer install -d /var/www/html
