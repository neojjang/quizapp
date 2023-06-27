FROM composer:latest as builder
WORKDIR /app
COPY . /app
RUN composer install \
     --ignore-platform-reqs \
     --no-interaction \
     --no-plugins \
     --no-scripts \
     --prefer-dist
# RUN composer install


FROM php:8.0-fpm
WORKDIR /app
COPY . /app
RUN apt-get update
RUN apt-get install -y --no-install-recommends \
       git \
       cron \
       libfreetype6-dev \
       libgmp-dev \
       libjpeg-dev \
       libmagickwand-dev \
       libmemcached-dev \
       libpng-dev \
       libpq-dev \
       libssl-dev \
       libxml2-dev \
       libz-dev \
       libzip-dev \
       nano \
       openssh-server \
       unzip \
       zlib1g-dev  \
       libonig-dev
RUN docker-php-ext-install mysqli pdo pdo_mysql mbstring zip xml bcmath

COPY --from=builder /app/vendor /app/vendor
COPY --from=builder /usr/bin/composer /usr/bin/composer
ENV TZ=Asia/Seoul
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime
RUN chmod 777 -R /app/storage/
RUN php artisan config:cache

CMD ["php", "artisan", "serve", "--host=0.0.0.0"]
