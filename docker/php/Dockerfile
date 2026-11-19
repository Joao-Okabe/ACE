FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends git libpq-dev libxml2-dev libonig-dev unzip \
    && docker-php-ext-install pdo_pgsql pgsql iconv dom mbstring \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer dentro do container
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configuração PHP personalizada
COPY docker/php/php.ini /usr/local/etc/php/php.ini

RUN sed -ri \
    -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

RUN printf '%s\n' \
    '<Directory /var/www/html/public>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' \
    'Alias /uploads/ /var/www/html/storage/upload/' \
    '<Directory /var/www/html/storage/upload>' \
    '    Options -Indexes' \
    '    Require all granted' \
    '</Directory>' \
    > /etc/apache2/conf-available/app.conf \
    && a2enconf app

WORKDIR /var/www/html

COPY web/back-end/composer.json web/back-end/composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-source --optimize-autoloader

EXPOSE 80
