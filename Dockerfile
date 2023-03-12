FROM php:7-apache

RUN apt-get update && apt-get install -y imagemagick wget git unzip libpng-dev
RUN docker-php-ext-install pdo pdo_mysql gd

RUN sed -ri -e 's!/var/www/html!/code/web!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/code/web!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

RUN mkdir /code
WORKDIR /code

RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/be31d0a5e5e835063c29bb45804bd94eefd4cf34/web/installer -O - -q | php -- --quiet

COPY composer.json composer.lock /code/
RUN php composer.phar --no-scripts --no-dev --optimize-autoloader install

COPY . /code

RUN chmod a+rwx /code/var/cache /code/var/logs

# docker run -it -p 8001:80 -v "$PWD/app/config/parameters.yml:/code/app/config/parameters.yml" gallery-api
