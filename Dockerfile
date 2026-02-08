FROM php:8.2-apache

# ===============================
# System & PHP extensions
# ===============================
RUN apt-get update && apt-get install -y \
    freetds-dev \
    freetds-bin \
    freetds-common \
    unixodbc \
    libsybdb5 \
    libpq-dev \
    libzip-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    openssl \
    zip \
    unzip \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    pdo_dblib \
    gd \
    zip \
    opcache \
 && rm -rf /var/lib/apt/lists/*

# ===============================
# Apache modules (INI KUNCI)
# ===============================
RUN a2enmod ssl rewrite headers deflate

# ===============================
# Document root
# (NOTE: akan dioverride oleh volume)
# ===============================
COPY ./app /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# ===============================
# Expose ports
# ===============================
EXPOSE 80 443
