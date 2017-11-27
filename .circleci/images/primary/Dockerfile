FROM php:7.0-apache

# Change docroot since we use Composer Drupal project.
RUN sed -ri -e 's!/var/www/html!/var/www/html/web!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www!/var/www/html/web!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt-get update && apt-get install -y \
    bzip2 \
    curl \
    git \
    libfontconfig \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    mariadb-client \
    unzip \
    vim \
    wget \
    zip \
    && docker-php-ext-install -j$(nproc) iconv mcrypt \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install pdo \
    && docker-php-ext-install pdo_mysql

# Install composer.
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/f3333f3bc20ab8334f7f3dada808b8dfbfc46088/web/installer -O - -q | php -- --quiet
RUN mv composer.phar /usr/local/bin/composer

# Put a turbo on composer.
RUN composer global require hirak/prestissimo

# Install XDebug.
RUN pecl install xdebug-2.5.5 \
    && docker-php-ext-enable xdebug

# Install Robo CI.
RUN wget http://robo.li/robo.phar
RUN chmod +x robo.phar && mv robo.phar /usr/local/bin/robo
