#!/bin/bash

# Laravel deployment build script
echo "Starting Laravel deployment build..."

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Create storage directories if they don't exist
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Create SQLite database
touch database/database.sqlite

# Generate application key
php artisan key:generate --force

# Set environment for production
export APP_ENV=production
export APP_DEBUG=false
export DB_CONNECTION=sqlite
export DB_DATABASE=database/database.sqlite

# Run database migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod 644 database/database.sqlite

echo "Laravel deployment build completed!"
