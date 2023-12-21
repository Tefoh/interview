FROM ubuntu:22.04

COPY . /var/www

ARG uid
ARG user

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get -y update && apt-get -y install software-properties-common \
    && add-apt-repository ppa:ondrej/php

RUN apt-get -y update && apt-get install -y \
    php8.2 \
    php8.2-fpm \
    php8.2-cli \
    php8.2-mysql \
    php8.2-curl \
    php8.2-sqlite3 \
    php8.2-xml \
    php8.2-bcmath \
    php8.2-zip \
    php8.2-mbstring \
    php8.2-intl \
    php8.2-ast

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ADD .docker/app/php-fpm.conf /etc/php/8.2/fpm/php-fpm.conf
ADD .docker/app/www.conf /etc/php/8.2/fpm/pool.d/www.conf


# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user /var/www

RUN mkdir -p /run/php/
RUN touch /run/php/php8.2-fpm.pid
RUN chmod +x /run/php/php8.2-fpm.pid
RUN chown 1000:1000 /run/php/php8.2-fpm.pid

# Set working directory
WORKDIR /var/www

# Run php-fpm
CMD ["php-fpm8.2", "-F"]

USER $user

EXPOSE 9000
