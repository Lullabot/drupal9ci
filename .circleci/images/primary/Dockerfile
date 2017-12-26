FROM php:7.0-apache

# Change docroot since we use Composer Drupal project.
RUN sed -ri -e 's!/var/www/html!/var/www/html/web!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www!/var/www/html/web!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install libraries and extensions.
RUN apt-get update && apt-get install -y \
    bzip2 \
    curl \
    git \
    imagemagick \
    libfontconfig \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmagickwand-dev \
    libmcrypt-dev \
    libpng-dev \
    mariadb-client \
    sudo \
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
# @TODO replace the following URL by http://robo.li/robo.phar when the Robo team fixes it.
RUN wget https://github.com/consolidation/Robo/releases/download/1.1.5/robo.phar
RUN chmod +x robo.phar && mv robo.phar /usr/local/bin/robo

# Install Dockerize.
ENV DOCKERIZE_VERSION v0.6.0
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz

# Install ImageMagic to take screenshots.
RUN pecl install imagick \
    && docker-php-ext-enable imagick
