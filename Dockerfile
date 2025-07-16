# Multi-stage Dockerfile for Laravel backend
FROM php:8.4-fpm as base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    unzip \
    git \
    curl \
    nginx \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo_mysql pdo_pgsql mbstring exif pcntl bcmath \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www

# Development stage
FROM base as development

# Install development dependencies
RUN apt-get update && apt-get install -y \
    vim \
    htop \
    && rm -rf /var/lib/apt/lists/*

# Create nginx config for development
RUN echo 'server {\n\
    listen 80;\n\
    server_name _;\n\
    root /var/www/public;\n\
    index index.php index.html;\n\
\n\
    # Enable CORS for development\n\
    add_header Access-Control-Allow-Origin "*" always;\n\
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;\n\
    add_header Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept, Authorization" always;\n\
\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
\n\
    location ~ \.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
        fastcgi_read_timeout 300;\n\
    }\n\
\n\
    location ~ /\.ht {\n\
        deny all;\n\
    }\n\
\n\
    # Health check endpoint\n\
    location /api/health {\n\
        access_log off;\n\
        return 200 "healthy";\n\
        add_header Content-Type text/plain;\n\
    }\n\
\n\
    client_max_body_size 100M;\n\
}' > /etc/nginx/sites-available/default

# Create supervisor config for development
RUN echo '[supervisord]\n\
nodaemon=true\n\
user=root\n\
\n\
[program:php-fpm]\n\
command=php-fpm\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
\n\
[program:nginx]\n\
command=nginx -g "daemon off;"\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0' > /etc/supervisor/conf.d/supervisord.conf

# Create development startup script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "Starting Laravel development environment..."\n\
\n\
# Create required directories first\n\
mkdir -p /var/www/bootstrap/cache\n\
mkdir -p /var/www/storage/logs\n\
mkdir -p /var/www/storage/framework/sessions\n\
mkdir -p /var/www/storage/framework/views\n\
mkdir -p /var/www/storage/framework/cache\n\
\n\
# Set proper permissions\n\
chown -R www-data:www-data /var/www\n\
chmod -R 775 /var/www/storage\n\
chmod -R 775 /var/www/bootstrap/cache\n\
\n\
# Install/update composer dependencies\n\
if [ -f "composer.json" ]; then\n\
    echo "Installing composer dependencies..."\n\
    composer install --no-interaction --prefer-dist\n\
fi\n\
\n\
# Wait for database to be ready\n\
echo "Waiting for database connection..."\n\
until php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null; do\n\
  echo "Database not ready, waiting 2 seconds..."\n\
  sleep 2\n\
done\n\
echo "Database connection established!"\n\
\n\
# Generate application key if not set\n\
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:your-local-key-here" ]; then\n\
  echo "Generating application key..."\n\
  php artisan key:generate --force\n\
fi\n\
\n\
# Run migrations for development\n\
echo "Running migrations..."\n\
php artisan migrate --force\n\
\n\
# Only run seeders if SEED_DATABASE environment variable is set to true\n\
if [ "$SEED_DATABASE" = "true" ]; then\n\
  echo "Running database seeders..."\n\
  php artisan db:seed --force\n\
else\n\
  echo "Skipping database seeding (set SEED_DATABASE=true to enable)"\n\
fi\n\
\n\
# Clear and cache config for development\n\
echo "Setting up development environment..."\n\
php artisan config:clear\n\
php artisan route:clear\n\
php artisan view:clear\n\
\n\
echo "Laravel development setup completed!"\n\
\n\
# Start supervisor\n\
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf' > /start-dev.sh \
    && chmod +x /start-dev.sh

# Set environment for development
ENV APP_ENV=local
ENV APP_DEBUG=true

# Expose port 80
EXPOSE 80

# Use development startup script
CMD ["/start-dev.sh"]

# Production stage
FROM base as production

# Copy the Laravel application files
COPY backend/ ./

# Create required directories before composer install
RUN mkdir -p /var/www/bootstrap/cache \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/framework/cache \
    && chmod -R 775 /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage

# Install Composer dependencies for production
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Remove any existing frontend build artifacts
RUN rm -rf ./public/frontend

# Create nginx config for production
RUN echo 'server {\n\
    listen 80;\n\
    server_name _;\n\
    root /var/www/public;\n\
    index index.php index.html;\n\
\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
\n\
    location ~ \.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
        fastcgi_read_timeout 300;\n\
    }\n\
\n\
    location ~ /\.ht {\n\
        deny all;\n\
    }\n\
\n\
    client_max_body_size 100M;\n\
}' > /etc/nginx/sites-available/default

# Create supervisor config for production
RUN echo '[supervisord]\n\
nodaemon=true\n\
user=root\n\
\n\
[program:php-fpm]\n\
command=php-fpm\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
\n\
[program:nginx]\n\
command=nginx -g "daemon off;"\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0' > /etc/supervisor/conf.d/supervisord.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Create startup script for production
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "Starting Laravel application setup..."\n\
\n\
# Wait for database to be ready\n\
echo "Waiting for database connection..."\n\
until php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null; do\n\
  echo "Database not ready, waiting 2 seconds..."\n\
  sleep 2\n\
done\n\
echo "Database connection established!"\n\
\n\
# Generate application key if not set\n\
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then\n\
  echo "Generating application key..."\n\
  php artisan key:generate --force\n\
else\n\
  echo "Application key already set"\n\
fi\n\
\n\
# Check if we should run fresh migrations (for first deployment)\n\
if [ "$RUN_FRESH_MIGRATION" = "true" ]; then\n\
  echo "Running fresh migrations with seeding..."\n\
  php artisan migrate:fresh --seed --force\n\
else\n\
  echo "Running regular migrations..."\n\
  php artisan migrate --force\n\
  \n\
  # Only run seeders if SEED_DATABASE is set to true\n\
  if [ "$SEED_DATABASE" = "true" ]; then\n\
    echo "Running database seeders..."\n\
    php artisan db:seed --force\n\
  else\n\
    echo "Skipping database seeding (set SEED_DATABASE=true to enable)"\n\
  fi\n\
fi\n\
\n\
# Clear and cache config\n\
echo "Optimizing application..."\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
echo "Laravel setup completed successfully!"\n\
\n\
# Start supervisor\n\
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf' > /start.sh \
    && chmod +x /start.sh

# Set environment for production
ENV APP_ENV=production
ENV APP_DEBUG=false

# Expose port 80 for Render
EXPOSE 80

# Use startup script that handles Laravel setup then starts supervisor
CMD ["/start.sh"]
