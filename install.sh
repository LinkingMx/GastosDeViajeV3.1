#!/bin/bash

echo "🚀 Installing Gastos de Viaje Application..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Run production seeders
echo "🌱 Seeding production data..."
php artisan db:seed --class=ProductionSeeder

# Run installation command
echo "⚙️ Running installation setup..."
php artisan app:install

# Cache optimization
echo "🚀 Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Installation completed successfully!"
echo "📋 Next steps:"
echo "   1. Configure your .env file"
echo "   2. Set up your web server"
echo "   3. Access the admin panel"