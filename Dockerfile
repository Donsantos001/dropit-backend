FROM richarvey/nginx-php-fpm:3.1.6

COPY . .

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Database config
ENV DB_CONNECTION pgsql
ENV DB_HOST db
ENV DB_PORT 5432
ENV DB_DATABASE dropit
ENV DB_USERNAME postgres
ENV DB_PASSWORD secret

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN chmod +x docker/entrypoint.sh
CMD ["docker/entrypoint.sh"]