#!/usr/bin/env bash
# EC2 上で実行されるデプロイ処理（GitHub Actions から ssh bash -s で stdin 経由投入）
set -euo pipefail

echo "==> [workflow commit: ${GITHUB_SHA}] (このコミットのワークフローで実行中)"
REPO_ROOT=/var/www/html
echo "==> cd ${REPO_ROOT}"
cd "${REPO_ROOT}" || { echo "::error::${REPO_ROOT} がありません。EC2 の user_data を確認してください。" && exit 1; }
# nginx 所有のリポジトリを root が操作するため Git 2.35+ の dubious ownership を毎コマンドで回避（--system 設定が効かない環境がある）
GIT_SAFE=(git -c safe.directory="${REPO_ROOT}")
if [ ! -d .git ]; then
  echo "==> git clone (初回)"
  sudo "${GIT_SAFE[@]}" clone -b main "https://github.com/${GITHUB_REPO}.git" .
fi
echo "==> git fetch & reset"
sudo "${GIT_SAFE[@]}" fetch origin main
sudo "${GIT_SAFE[@]}" reset --hard origin/main

echo "==> apply .env from GitHub Actions (SSM 由来)"
if [ ! -f /tmp/.env.deploy ]; then
  echo "::error::/tmp/.env.deploy がありません。deploy ワークフローで SSM から生成・scp してください。"
  exit 1
fi
sudo cp /tmp/.env.deploy /var/www/html/src/.env
sudo chown nginx:nginx /var/www/html/src/.env
sudo chmod 640 /var/www/html/src/.env
PUBLIC_IP=$(curl -s --connect-timeout 2 http://169.254.169.254/latest/meta-data/public-ipv4 || true)
if [ -n "$PUBLIC_IP" ]; then
  sudo sed -i "s|^APP_URL=.*|APP_URL=http://$PUBLIC_IP|" /var/www/html/src/.env
fi

echo "==> ensure PHP 8.4 のみ（8.5 との衝突は --allowerasing で解消）"
sudo dnf install -y php8.4 php8.4-cli php8.4-fpm php8.4-mysqlnd php8.4-xml php8.4-mbstring php8.4-zip --allowerasing
sudo systemctl stop php-fpm 2>/dev/null || true
for FPM_WWW in /etc/php-fpm.d/www.conf /etc/php8.4/php-fpm.d/www.conf; do
  if sudo test -f "$FPM_WWW"; then
    sudo sed -i 's|^listen = .*|listen = 127.0.0.1:9000|' "$FPM_WWW"
    # AL2023 既定は apache 等のことが多く、storage を nginx 所有にすると書き込みで 500 になる
    sudo sed -i 's/^user = .*/user = nginx/' "$FPM_WWW"
    sudo sed -i 's/^group = .*/group = nginx/' "$FPM_WWW"
  fi
done
sudo systemctl daemon-reload
sudo systemctl enable php-fpm 2>/dev/null || true
sudo systemctl restart php-fpm

echo "==> SELinux: php-fpm から DB 外向き接続を許可（Enforcing かつ未 ON のときのみ -P）"
if command -v getenforce >/dev/null 2>&1 && [ "$(getenforce 2>/dev/null)" = "Enforcing" ]; then
  if getsebool httpd_can_network_connect_db 2>/dev/null | grep -q 'off$'; then
    sudo setsebool -P httpd_can_network_connect_db 1
  fi
  if getsebool httpd_can_network_connect 2>/dev/null | grep -q 'off$'; then
    sudo setsebool -P httpd_can_network_connect 1
  fi
fi

echo "==> ensure nginx vhost（Laravel / user_data 未反映時のデフォルトページを防ぐ）"
sudo tee /etc/nginx/conf.d/atte.conf > /dev/null <<'NGINX_ATTE'
server {
    listen 80 default_server;
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
NGINX_ATTE
sudo rm -f /etc/nginx/conf.d/default.conf
sudo nginx -t
sudo systemctl reload nginx

echo "==> resolve PHP CLI（AL2023 は /usr/bin/php のみの場合あり）"
PHP_CLI=""
for c in /usr/bin/php8.4 /usr/bin/php; do
  if sudo test -x "$c"; then PHP_CLI=$c; break; fi
done
if [ -z "$PHP_CLI" ]; then
  echo "::error::PHP CLI が見つかりません（php8.4-cli を確認してください）"
  exit 1
fi
echo "Using: $PHP_CLI ($("$PHP_CLI" -r 'echo PHP_VERSION;' 2>/dev/null || echo unknown))"
sudo "$PHP_CLI" -r 'if (version_compare(PHP_VERSION, "8.4.0", "<") || version_compare(PHP_VERSION, "8.5.0", ">=")) { fwrite(STDERR, "PHP 8.4.x が必要です。現在: ".PHP_VERSION."\n"); exit(1); }'

echo "==> ensure Composer"
if ! sudo test -x /usr/local/bin/composer; then
  echo "Installing Composer..."
  sudo bash -c "cd /tmp && curl -sS https://getcomposer.org/installer | ${PHP_CLI} && mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer"
fi
sudo "$PHP_CLI" /usr/local/bin/composer --version || { echo "::error::Composer の確認に失敗しました。" && exit 1; }
echo "==> cd src && composer install"
cd src
echo "==> ensure Laravel storage dirs"
sudo mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache
sudo COMPOSER_ALLOW_SUPERUSER=1 "$PHP_CLI" /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction
echo "==> migrate"
sudo "$PHP_CLI" artisan migrate --force
echo "==> ownership for nginx + php-fpm (vendor 含む)"
sudo chown -R nginx:nginx /var/www/html
sudo chmod -R 775 /var/www/html/src/storage /var/www/html/src/bootstrap/cache
echo "==> clear caches (FPM と同じユーザー)"
sudo -u nginx env HOME=/var/lib/nginx "$PHP_CLI" /var/www/html/src/artisan optimize:clear 2>/dev/null || true
echo "==> restart php-fpm nginx"
sudo systemctl restart php-fpm nginx
echo "==> Deploy done"
