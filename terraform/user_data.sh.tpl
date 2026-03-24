#!/bin/bash
set -e
export DEBIAN_FRONTEND=noninteractive

# Amazon Linux 2023: PHP 8.4 を明示（デフォルトの php は 8.5 になり composer.lock と不整合になるため）
if [ -f /etc/amazon-linux-release ]; then
  dnf update -y
  dnf install -y nginx git \
    php8.4 php8.4-cli php8.4-fpm php8.4-mysqlnd php8.4-xml php8.4-mbstring php8.4-zip
  # 既存の php-fpm（別バージョン）があれば止める
  systemctl stop php-fpm 2>/dev/null || true
  systemctl disable php-fpm 2>/dev/null || true
  # FPM が 127.0.0.1:9000 で待ち受け（nginx と一致）
  for FPM_WWW in /etc/php-fpm.d/www.conf /etc/php8.4/php-fpm.d/www.conf; do
    if [ -f "$FPM_WWW" ]; then
      sed -i 's|^listen = .*|listen = 127.0.0.1:9000|' "$FPM_WWW"
    fi
  done
  systemctl enable nginx php8.4-fpm
  systemctl start php8.4-fpm
fi

# Composer（PHP 8.4 で実行）
curl -sS https://getcomposer.org/installer | /usr/bin/php8.4
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# アプリ配置ディレクトリ
mkdir -p /var/www/html
chown -R nginx:nginx /var/www/html 2>/dev/null || true

# .env を配置（デプロイ時に上書き可能）
mkdir -p /var/www/html/src
cat > /var/www/html/src/.env << 'ENVFILE'
APP_NAME=Atte
APP_ENV=production
APP_DEBUG=false
APP_URL=${app_url}
APP_KEY=${app_key}

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=${db_host}
DB_PORT=3306
DB_DATABASE=${db_name}
DB_USERNAME=${db_username}
DB_PASSWORD=${db_password}

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
ENVFILE

# APP_URL を EC2 のパブリック IP に（メタデータから取得）
PUBLIC_IP=$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4 || echo "localhost")
sed -i "s|APP_URL=.*|APP_URL=http://$PUBLIC_IP|" /var/www/html/src/.env

# Nginx 設定（Laravel document root: src/public）
cat > /etc/nginx/conf.d/atte.conf << 'NGINX'
server {
    listen 80;
    server_name _;
    root /var/www/html/src/public;
    index index.php;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
NGINX

rm -f /etc/nginx/conf.d/default.conf 2>/dev/null || true
systemctl start nginx
systemctl restart nginx php8.4-fpm 2>/dev/null || true
