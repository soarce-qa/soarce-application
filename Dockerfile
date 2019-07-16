FROM php:7.2-fpm

MAINTAINER Holger Segnitz <holger@segnitz.net>

RUN apt-get update -y && apt-get install -y libxslt-dev

# RUN pecl install -f -o redis xdebug
# RUN docker-php-ext-enable redis.so xdebug.so

RUN docker-php-ext-install exif mysqli xsl
# RUN docker-php-ext-configure gd --with-jpeg-dir --with-png-dir --with-webp--dir
# RUN docker-php-ext-install gd

ADD .docker/docker-php.ini /usr/local/etc/php/conf.d/docker-php.ini
ADD .docker/fpm.conf /usr/local/etc/php-fpm.d/zzz-fpm.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
