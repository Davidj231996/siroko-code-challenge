FROM php:8.1-apache

EXPOSE 8000

RUN a2enmod rewrite

RUN docker-php-ext-install pdo pdo_mysql mysqli opcache

RUN apt-get update && apt-get install -y \
    gnupg \
    g++ \
    procps \
    openssl \
    git \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libicu-dev  \
    libonig-dev \
    libxslt1-dev \
    acl

COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/apache.conf /etc/apache2/sites-enabled/000-default.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html/

CMD ["/var/www/html/docker/php/init.sh"]