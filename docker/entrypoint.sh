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

echo "Migrating"
php artisan migrate --force

echo "Generating key"
php artisan key:generate

echo "Clearing cache"
php artisan cache:clear
php artisan config:clear
php artisan route:clear

echo "Starting server"
php artisan serve --host=0.0.0.0 --port=8000 --env=.env