FROM php:7.0-apache

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html

RUN sudo -u www-data composer install -d /var/www/html

COPY vhost.conf /etc/apache2/sites-enabled/praatmee.conf
