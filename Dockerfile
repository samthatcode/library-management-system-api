# Use Render's recommended PHP-FPM + Nginx image
FROM render-examples/php-nginx-fpm:latest

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Optimize Laravel for production
RUN php artisan config:cache && php artisan route:cache

# Expose port 10000 (Render routes traffic here)
EXPOSE 10000
