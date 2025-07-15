FROM php:8.4.4-fpm

COPY php.ini /usr/local/etc/php/

# Node.js のインストール
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt update \
    && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip nodejs \
    && docker-php-ext-install pdo_mysql zip

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer self-update

WORKDIR /var/www
