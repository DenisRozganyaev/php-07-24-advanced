FROM php:8.3-apache

#RUN pecl install xdebug

RUN docker-php-ext-install -j$(nproc) pdo_mysql

RUN apt-get update && apt-get upgrade -y && apt-get install -y git

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#COPY ./xdebug.ini "${PHP_INI_DIR}/conf.d"

WORKDIR /var/www

RUN /usr/sbin/a2enmod rewrite

COPY default.conf /etc/apache2/sites-available/000-default.conf
