FROM php:7.4-fpm

# Install "curl", "libmemcached-dev", "libpq-dev", "libjpeg-dev",
#         "libpng-dev", "libfreetype6-dev", "libssl-dev", "libmcrypt-dev",
RUN set -xe && \
  apt-get update && \
  apt-get upgrade -y && \
  apt-get install -y --no-install-recommends \
    curl \
    libmemcached-dev \
    libzip-dev \
    libpq-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    libssl-dev \
    libmcrypt-dev \
  && rm -rf /var/lib/apt/lists/*

# Install the PHP pdo_mysql extention
RUN docker-php-ext-install pdo_mysql \
    mysqli \
    pdo \
    pdo_mysql \
    json \
    zip \
    pcntl \
    bcmath \
# Install the PHP gd library
&& docker-php-ext-configure gd \
--prefix=/usr \
--with-jpeg \
--with-freetype && \
docker-php-ext-install gd

# Add Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1


WORKDIR /var/www

COPY . . 

RUN set -eux; \
   composer install --prefer-dist --no-scripts --no-progress --no-suggest;
   #composer clear-cache

#CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

EXPOSE 9000

CMD ["php-fpm"]






