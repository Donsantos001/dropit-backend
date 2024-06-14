#!/bin/bash


echo "Composer installing"
if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction
fi

if [ ! -f ".env" ]; then
    echo "Creating env file for env $APP_ENV"
    cp .env.example .env
else
    echo "env file exists."
fi

# composer global require hirak/prestissimo
# composer install --no-dev --working-dir=/var/www/html

echo "Migrating"
yes | php artisan migrate

echo "Generating key"
php artisan key:generate

echo "Clearing cache"
php artisan cache:clear
php artisan config:clear
php artisan route:clear

echo "Starting server"
php artisan serve --port=$PORT --host=0.0.0.0 --env=.env
# exec docker-php-entrypoint "$@"