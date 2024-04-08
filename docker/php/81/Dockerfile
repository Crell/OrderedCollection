FROM php:8.1-cli
WORKDIR /usr/src/myapp

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install zip unzip git zlib1g-dev -y \
    && pecl install xdebug \
    && pecl install pcov \
    && git clone https://github.com/NoiseByNorthwest/php-spx.git -b release/latest \
    && cd php-spx  \
    && phpize  \
    && ./configure  \
    && make  \
    && make install
