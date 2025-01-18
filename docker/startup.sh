#!/bin/sh

#sed -i "s,LISTEN_PORT,$PORT,g" /etc/nginx/nginx.conf

php-fpm -D

while ! nc -w 1 -z 127.0.0.1 9000; do sleep 0.1; done;

nginx

cd /var/www/html/laravel_app && /usr/local/bin/composer update --no-dev

supervisord --nodaemon -c /etc/supervisor/conf.d/supervisor.conf

php artisan storage:link
php artisan migrate
php artisan db:seed
php artisan optimize:clear