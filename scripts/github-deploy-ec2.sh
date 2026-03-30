#!/usr/bin/env bash
# EC2 上で実行されるデプロイ処理（GitHub Actions から ssh bash -s で stdin 経由投入）
set -euo pipefail

echo "==> [workflow commit: ${GITHUB_SHA}] (このコミットのワークフローで実行中)"
REPO_ROOT=/var/www/html
SRC_DIR="${REPO_ROOT}/src"
echo "==> cd ${REPO_ROOT}"
cd "${REPO_ROOT}" || { echo "::error::${REPO_ROOT} がありません。EC2 の user_data を確認してください。" && exit 1; }
# nginx 所有のリポジトリを root が操作するため Git 2.35+ の dubious ownership を毎コマンドで回避
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
sudo cp /tmp/.env.deploy "${SRC_DIR}/.env"
sudo chown nginx:nginx "${SRC_DIR}/.env"
sudo chmod 640 "${SRC_DIR}/.env"
PUBLIC_IP=$(curl -s --connect-timeout 2 http://169.254.169.254/latest/meta-data/public-ipv4 || true)
if [ -n "$PUBLIC_IP" ]; then
  sudo sed -i "s|^APP_URL=.*|APP_URL=http://$PUBLIC_IP|" "${SRC_DIR}/.env"
fi

echo "==> ensure PHP 8.4 のみ（8.5 との衝突は --allowerasing で解消）"
sudo dnf install -y php8.4 php8.4-cli php8.4-fpm php8.4-mysqlnd php8.4-xml php8.4-mbstring php8.4-zip --allowerasing

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

echo "==> php-fpm: Laravel 8 + PHP 8.4 向け error_reporting（非推奨を FPM で抑制）"
ERCODE=$(sudo "$PHP_CLI" -r 'echo E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED;')
sudo tee /etc/php-fpm.d/99-atte-errorreporting.conf >/dev/null <<EOF
[www]
php_admin_value[error_reporting] = ${ERCODE}
php_admin_flag[log_errors] = on
EOF

sudo systemctl stop php-fpm 2>/dev/null || true
for FPM_WWW in /etc/php-fpm.d/www.conf /etc/php8.4/php-fpm.d/www.conf; do
  if sudo test -f "$FPM_WWW"; then
    sudo sed -i 's|^listen = .*|listen = 127.0.0.1:9000|' "$FPM_WWW"
    sudo sed -i 's/^user = .*/user = nginx/' "$FPM_WWW"
    sudo sed -i 's/^group = .*/group = nginx/' "$FPM_WWW"
  fi
done
sudo systemctl daemon-reload
sudo systemctl enable php-fpm 2>/dev/null || true
sudo systemctl restart php-fpm

echo "==> SELinux: Web→RDS（getsebool の表記ゆれを避け Enforcing 時は毎回 setsebool）"
if command -v getenforce >/dev/null 2>&1 && [ "$(getenforce 2>/dev/null)" = "Enforcing" ]; then
  sudo setsebool -P httpd_can_network_connect_db 1
  sudo setsebool -P httpd_can_network_connect 1
  echo "SELinux=$(getenforce) httpd_can_network_connect_db=$(getsebool -n httpd_can_network_connect_db 2>/dev/null || echo '?')"
fi

echo "==> ensure nginx vhost（Laravel document root）"
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
        try_files $uri =404;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
NGINX_ATTE
sudo rm -f /etc/nginx/conf.d/default.conf
sudo nginx -t
sudo systemctl reload nginx

echo "==> ensure Composer"
if ! sudo test -x /usr/local/bin/composer; then
  echo "Installing Composer..."
  sudo bash -c "cd /tmp && curl -sS https://getcomposer.org/installer | ${PHP_CLI} && mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer"
fi
sudo "$PHP_CLI" /usr/local/bin/composer --version || { echo "::error::Composer の確認に失敗しました。" && exit 1; }

echo "==> cd src && composer install"
cd "${SRC_DIR}"
echo "==> ensure Laravel storage dirs + laravel.log"
sudo mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache
sudo touch storage/logs/laravel.log
sudo COMPOSER_ALLOW_SUPERUSER=1 "$PHP_CLI" /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction

echo "==> migrate"
sudo "$PHP_CLI" artisan migrate --force

echo "==> demo login user (README の yamada@example.com / password を冪等で確保)"
sudo "$PHP_CLI" artisan db:seed --class=DemoLoginUserSeeder --force

echo "==> ownership for nginx + php-fpm (vendor 含む)"
sudo chown -R nginx:nginx "${REPO_ROOT}"
sudo chmod -R 775 "${SRC_DIR}/storage" "${SRC_DIR}/bootstrap/cache"
sudo chmod 664 "${SRC_DIR}/storage/logs/laravel.log" 2>/dev/null || true

echo "==> clear caches (FPM と同じユーザー)"
sudo -u nginx env HOME="${REPO_ROOT}" "$PHP_CLI" "${SRC_DIR}/artisan" optimize:clear 2>/dev/null || true

echo "==> diagnostic: DB を nginx ユーザーで確認（Web と同じ権限）"
sudo -u nginx env HOME="${REPO_ROOT}" "$PHP_CLI" "${SRC_DIR}/artisan" migrate:status 2>&1 | tail -40 || true

echo "==> restart php-fpm nginx"
sudo systemctl restart php-fpm nginx

echo "==> post-deploy: localhost HTTP + ログ（500 の直接原因を Actions に出力）"
CODE=$(curl -s -o /tmp/__atte_curl_body.txt -w "%{http_code}" http://127.0.0.1/ || echo "000")
echo "curl_http_code=${CODE}"
if [ "${CODE}" != "200" ] && [ "${CODE}" != "302" ] && [ "${CODE}" != "301" ]; then
  echo "::warning::127.0.0.1 の応答が ${CODE}（200/302/301 以外はアプリまたは FPM エラーの可能性）"
  echo "---- response body (先頭 4000 文字) ----"
  head -c 4000 /tmp/__atte_curl_body.txt 2>/dev/null || true
  echo ""
  echo "---- tail ${SRC_DIR}/storage/logs/laravel.log ----"
  sudo tail -n 120 "${SRC_DIR}/storage/logs/laravel.log" 2>/dev/null || echo "(laravel.log なし)"
  echo "---- tail /var/log/nginx/error.log ----"
  sudo tail -n 80 /var/log/nginx/error.log 2>/dev/null || true
fi

echo "==> Deploy done"
