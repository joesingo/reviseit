FROM php:5-apache
COPY website/ /var/www/html/
COPY config/php5.ini /usr/local/etc/php/conf.d/php5.ini
RUN docker-php-ext-install mysqli
