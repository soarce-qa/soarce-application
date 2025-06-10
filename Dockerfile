FROM php:8.4-fpm-bookworm

RUN apt-get update -y && apt-get install -y libxslt-dev && apt-get clean && rm -rf /var/lib/apt/lists

# RUN pecl install -f -o redis xdebug
# RUN docker-php-ext-enable redis.so xdebug.so

RUN docker-php-ext-install mysqli xsl intl
# RUN docker-php-ext-configure gd --with-jpeg-dir --with-png-dir --with-webp--dir
# RUN docker-php-ext-install gd

RUN ln -s /usr/local/bin/php /usr/local/bin/php8.4

ADD .docker/docker-php.ini /usr/local/etc/php/conf.d/docker-php.ini
ADD .docker/fpm.conf /usr/local/etc/php-fpm.d/zzz-fpm.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
