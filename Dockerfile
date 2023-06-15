FROM php:7.4-fpm-alpine

LABEL maintainer="pyaehein000@gmail.com"

ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html

RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ gnu-libiconv
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php

ADD https://dl.bintray.com/php-alpine/key/php-alpine.rsa.pub /etc/apk/keys/php-alpine.rsa.pub
RUN apk --update add ca-certificates
RUN echo "https://dl.bintray.com/php-alpine/v3.10/php-7.4" >> /etc/apk/repositories

# packages
RUN apk update --update -q && apk add -q --no-cache \
	bash \
	vim \
	supervisor \
	git \
	tzdata \
	gettext \
	curl \
	# php
	php \
    php-gd \
    php-bcmath \
    php-json \ 
    php-ctype \
    php-iconv \
    php-calendar \
    php-zip \
	php-curl \
	php-dom \
	php-fpm \
	php-gettext \
	php-json \
	php-pcntl \
	php-posix \
	php-mbstring \
	php-openssl \
	php-pdo \
    php-pdo_mysql \
	php-phar \
	php-opcache \
	php-session \
	php-xml \
	php-zlib \
	&& rm -rf /var/cache/apk/*

# directory links
RUN ln -s /etc/php7 /etc/php && \
	ln -s /usr/bin/php7 /usr/bin/php && \
	ln -s /usr/sbin/php-fpm7 /usr/bin/php-fpm && \
	ln -s /usr/lib/php7 /usr/lib/php

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

COPY . /var/www/html

EXPOSE 9000

CMD ["php-fpm"]