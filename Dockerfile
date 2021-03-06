FROM php:7.4-apache

ENV APACHE_DOCUMENT_ROOT=/vaw/www/html/public
ENV PHPCS_VERSION=3.5.4

RUN echo "$(curl -sS https://composer.github.io/installer.sig) -" > composer-setup.php.sig \
        && curl -sS https://getcomposer.org/installer | tee composer-setup.php | sha384sum -c composer-setup.php.sig \
        && php composer-setup.php && rm composer-setup.php* \
        && chmod +x composer.phar && mv composer.phar /usr/bin/composer \
        && apt-get update \
        && pecl install xdebug \
        && apt-get install -y libzip-dev zip \
        && docker-php-ext-install pdo_mysql zip

WORKDIR /var/www/html

COPY . .
COPY .docker/default.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/xdebug.ini /usr/local/etc/php/conf.d/

RUN composer install
RUN a2enmod rewrite

RUN chown -R www-data:www-data config/jwt
RUN chown -R www-data:www-data var

EXPOSE 80

CMD /usr/sbin/apache2ctl -D FOREGROUND
