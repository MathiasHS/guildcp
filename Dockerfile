FROM php:7.1.1-apache

# Install required packages
RUN apt-get update && \
  apt-get install -y git zip unzip

# Configure PHP
COPY conf/errors.ini /usr/local/etc/php/conf.d/

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) pdo pdo_mysql mysqli  

# Install Apache extensions
RUN a2enmod rewrite && service apache2 restart

# Install PHP Composer and dependencies
WORKDIR /var/www
RUN curl -o composer-setup.php https://getcomposer.org/installer && \
  php composer-setup.php && \
  rm composer-setup.php && \
  mv composer.phar /usr/local/bin/composer

COPY composer.json composer.lock ./
RUN composer install

WORKDIR /var/www/html
COPY public/ /var/www/html