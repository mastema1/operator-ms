#!/bin/bash

echo "Starting Laravel Production Optimizations..."
echo

echo "1. Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo
echo "2. Running database migrations (if needed)..."
php artisan migrate --force

echo
echo "3. Caching configuration..."
php artisan config:cache

echo
echo "4. Caching routes..."
php artisan route:cache

echo
echo "5. Caching views..."
php artisan view:cache

echo
echo "6. Running general optimization..."
php artisan optimize

echo
echo "7. Optimizing Composer autoloader..."
composer install --optimize-autoloader --no-dev

echo
echo "Production optimizations completed!"
echo "Your Laravel application is now optimized for production."
