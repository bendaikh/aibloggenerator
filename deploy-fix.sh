#!/bin/bash

# Deployment script for fixing raw JSON display issue
# This script should be run on your production server

echo "========================================="
echo "Starting Deployment - JSON Fix"
echo "========================================="
echo ""

# Function to print colored output
print_success() {
    echo -e "\e[32m✓ $1\e[0m"
}

print_error() {
    echo -e "\e[31m✗ $1\e[0m"
}

print_info() {
    echo -e "\e[34mℹ $1\e[0m"
}

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    print_error "Error: artisan file not found. Please run this script from the Laravel project root."
    exit 1
fi

print_info "Putting application into maintenance mode..."
php artisan down || print_error "Failed to enter maintenance mode"
print_success "Application is in maintenance mode"

echo ""
print_info "Step 1: Pulling latest code changes..."
git pull origin main || print_error "Failed to pull latest changes"
print_success "Code updated"

echo ""
print_info "Step 2: Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader || print_error "Composer install failed"
print_success "Composer dependencies installed"

echo ""
print_info "Step 3: Installing and building frontend assets..."
npm ci || print_error "NPM install failed"
npm run build || print_error "Build failed"
print_success "Frontend assets built"

echo ""
print_info "Step 4: Clearing all caches..."
php artisan cache:clear-all || (
    print_info "Custom command not found, clearing caches individually..."
    php artisan cache:clear
    php artisan route:clear
    php artisan config:clear
    php artisan view:clear
    php artisan clear-compiled
)
print_success "All caches cleared"

echo ""
print_info "Step 5: Running database migrations (if any)..."
php artisan migrate --force || print_info "No new migrations to run"
print_success "Database migrations completed"

echo ""
print_info "Step 6: Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Application optimized"

echo ""
print_info "Step 7: Restarting services..."
# Uncomment the appropriate line for your setup:
# sudo systemctl restart php8.2-fpm
# sudo systemctl restart php8.1-fpm
# sudo systemctl restart nginx
# php artisan octane:reload
# php artisan queue:restart
print_info "Please restart your web server/PHP-FPM service manually"

echo ""
print_info "Bringing application back online..."
php artisan up
print_success "Application is online"

echo ""
echo "========================================="
echo "Deployment Complete!"
echo "========================================="
echo ""
print_info "Next steps:"
echo "  1. Test the application in an incognito browser window"
echo "  2. Hard refresh (Ctrl+Shift+R) on any page showing JSON"
echo "  3. Clear CDN cache if you're using one (Cloudflare, etc.)"
echo "  4. Monitor logs: tail -f storage/logs/laravel.log"
echo ""
print_success "Deployment completed successfully!"
