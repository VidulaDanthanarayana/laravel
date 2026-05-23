#!/bin/bash
# Azure App Service (Linux, PHP 8.3 + nginx) startup script.

APP_DIR="/home/site/wwwroot"
cd "$APP_DIR"

# ── 1. Configure nginx to serve from Laravel's public/ folder ─────────────────
cat > /etc/nginx/conf.d/default.conf <<'NGINX'
server {
    listen 8080;
    root /home/site/wwwroot/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX

nginx -s reload 2>/dev/null || true

# ── 2. Ensure storage directories exist ──────────────────────────────────────
mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p database
touch database/database.sqlite

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
