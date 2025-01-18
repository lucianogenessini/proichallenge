FROM php:8.3-fpm-alpine

RUN apk --no-cache add \
    zlib-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    supervisor \
    npm


RUN docker-php-ext-install pdo pdo_mysql zip exif pcntl bcmath gd

RUN apk add --no-cache nginx wget

RUN mkdir -p /run/nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

#SUPERVISOR
RUN mkdir -p /var/log/supervisor
COPY docker/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

#COPY PROJECT
RUN mkdir -p /app
COPY . /app

#COMPOSER
RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"
RUN cd /app && \
    /usr/local/bin/composer update --no-dev

#PERMISSIONS
RUN chown -R www-data: /app

#SUPERVISOR, SE LEVANTA PHP, NGINX, WORKERS
#CMD /usr/bin/supervisord --nodaemon -c /etc/supervisor/conf.d/supervisor.conf
CMD sh /app/docker/startup.sh