FROM php:7.0-apache

RUN a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

RUN apt-get update -y && \
    apt-get install zip -y

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html

COPY vhost.conf /etc/apache2/sites-enabled/clarity.conf

USER www-data

RUN composer install -d /var/www/html

CMD /user/sbin/apache2ctl -D FOREGROUND
