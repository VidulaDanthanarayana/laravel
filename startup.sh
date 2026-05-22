#!/bin/bash
# Azure App Service (Linux, PHP 8.3) startup script.
# Set in Azure Portal: Configuration > General settings > Startup Command
#   /home/site/wwwroot/startup.sh

APP_DIR="/home/site/wwwroot"

# ── 1. Point Apache at Laravel's public/ folder ──────────────────────────────
cat > /etc/apache2/sites-available/000-default.conf <<'VHOST'
<VirtualHost *:8080>
    DocumentRoot /home/site/wwwroot/public

    <Directory /home/site/wwwroot/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    ErrorLog  /home/LogFiles/apache_error.log
    CustomLog /home/LogFiles/apache_access.log combined
</VirtualHost>
VHOST

a2enmod rewrite

# ── 2. Install dependencies ───────────────────────────────────────────────────
cd "$APP_DIR"
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# ── 3. Bootstrap Laravel ──────────────────────────────────────────────────────
php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan storage:link --force

# ── 4. Permissions ────────────────────────────────────────────────────────────
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# ── 5. Restart Apache ─────────────────────────────────────────────────────────
service apache2 restart
