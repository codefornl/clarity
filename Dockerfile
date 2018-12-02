FROM php:7.0-apache

RUN a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

RUN apt-get update -y && \
    apt-get install zip -y && \
    apt-get install ca-certificates

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html

COPY vhost.conf /etc/apache2/sites-enabled/000-default.conf

USER www-data

RUN composer install -d /var/www/html

VOLUME /var/www/html/public
VOLUME /var/www/html/private

ENV BASE_URI https://cbase.codefor.nl
USER root
