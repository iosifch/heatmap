FROM php:8.0-alpine

ENV COMPOSER_HOME=/tmp/composer
ENV COMPOSER_MEMORY_LIMIT=-1

RUN apk update && apk upgrade \
    && apk add --no-cache git zip openssh autoconf g++ make openssl-dev

RUN pecl -q install apfd mongodb \
    && docker-php-ext-install -j$(nproc) bcmath opcache exif > /dev/null \
    && docker-php-ext-enable mongodb apfd opcache

RUN set -uex; \
    umask 0002; \
	  curl --silent --show-error https://getcomposer.org/composer-1.phar -o /usr/local/bin/composer \
    && chmod 0755 /usr/local/bin/composer \
    && composer global require hirak/prestissimo

WORKDIR /var/www/html

CMD ["php", "-S", "0.0.0.0:8080", "-t", "symfony/public"]
