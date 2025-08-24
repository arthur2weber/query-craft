# Dockerfile to run PHPUnit for this project in an isolated container
FROM php:8.3-cli

# Install system dependencies and PHP extensions (mbstring + zip + pcntl)
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
       git \
       unzip \
       libzip-dev \
       libonig-dev \
       libxml2-dev \
       ca-certificates \
       autoconf \
       gcc \
       make \
  && docker-php-ext-install zip mbstring pdo pdo_mysql pcntl xml \
  && rm -rf /var/lib/apt/lists/*

# Install Xdebug (for code coverage) via PECL and enable it
RUN pecl install xdebug || true \
  && printf "zend_extension=%s\nxdebug.mode=coverage\nxdebug.start_with_request=no\n" /usr/local/lib/php/extensions/no-debug-non-zts-20230831/xdebug.so > /usr/local/etc/php/conf.d/20-xdebug.ini \
  && docker-php-ext-enable xdebug || true

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock /app/

# Install PHP deps
RUN composer install --no-interaction --prefer-dist --no-scripts --no-progress || true

# Copy rest of the project
COPY . /app

# Ensure vendor is present (if not already installed, install now)
RUN if [ ! -d vendor ]; then composer install --no-interaction --prefer-dist --no-progress; fi

# Default command: run phpunit
CMD ["./vendor/bin/phpunit", "--colors=always"]
