#!/bin/bash
# Nginx
cat <<EOF > manager.test
server {
    listen 80;
    listen [::]:80;
    server_name manager.test;
    root /var/www/projects/ubuntu-ansible-developer/manager/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_index index.php;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

sudo mv manager.test /etc/nginx/sites-available/manager.test
sudo ln -sf /etc/nginx/sites-available/manager.test /etc/nginx/sites-enabled/manager.test
sudo systemctl reload nginx

# Hosts
if ! grep -q "manager.test" /etc/hosts; then
    echo "127.0.0.1 manager.test" | sudo tee -a /etc/hosts
fi

# Database
sudo -u postgres psql -c "CREATE USER manager WITH PASSWORD 'secret';" || true
sudo -u postgres psql -c "CREATE DATABASE manager OWNER manager;" || true

# Env
cd /var/www/projects/ubuntu-ansible-developer/manager
sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=pgsql/" .env
sed -i "s/^DB_HOST=.*/DB_HOST=127.0.0.1/" .env
sed -i "s/^DB_PORT=.*/DB_PORT=5432/" .env
sed -i "s/^DB_DATABASE=.*/DB_DATABASE=manager/" .env
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=manager/" .env
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=secret/" .env
# Set APP_URL
sed -i "s|^APP_URL=.*|APP_URL=http://manager.test|" .env

# Fix permissions
sudo chown -R alp:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

php artisan migrate --force
