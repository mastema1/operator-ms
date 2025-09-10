#!/bin/bash

# Laravel deployment build script
echo "Starting Laravel deployment build..."

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Create SQLite database if it doesn't exist
touch /tmp/database.sqlite

# Generate application key if not set
php artisan key:generate --force

# Run database migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod 666 /tmp/database.sqlite

echo "Laravel deployment build completed!"
