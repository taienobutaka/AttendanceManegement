# 出席管理システム開発用Makefile
.PHONY: init fresh restart up down cache stop clean logs shell mysql-shell test npm-install npm-build npm-dev composer-update composer-install backup help

# デフォルトターゲット
.DEFAULT_GOAL := help

# 初期化（開発環境セットアップ）
init:
	@echo "=== 出席管理システム開発環境セットアップ開始 ==="
	
	@echo "=== MySQL設定ファイル権限修正 ==="
	@chmod 644 ./docker/mysql/my.cnf
	
	docker-compose up -d --build

	@echo "=== PHPコンテナの起動待ち ==="
	@until docker-compose exec php php -v > /dev/null 2>&1; do \
		echo "Waiting for PHP container..."; \
		sleep 2; \
	done
	@sleep 3

	@echo "=== MySQLの起動待ち ==="
	@until docker-compose exec mysql mysqladmin ping -hmysql -uroot -proot --silent; do \
		echo "Waiting for MySQL..."; \
		sleep 2; \
	done
	@sleep 3

	@echo "=== PHP依存パッケージ更新・インストール ==="
	docker-compose exec php composer update
	docker-compose exec php composer install

	@echo "=== .envファイル作成 ==="
	@if [ ! -f src/.env ]; then cp src/.env.example src/.env; fi

	@echo "=== .envファイル自動修正 ==="
	sed -i 's/^DB_DATABASE=.*/DB_DATABASE=laravel_db/' src/.env
	sed -i 's/^DB_USERNAME=.*/DB_USERNAME=laravel_user/' src/.env
	sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=laravel_pass/' src/.env
	sed -i 's/^DB_HOST=.*/DB_HOST=mysql/' src/.env

	@echo "=== MailHog用メール設定自動修正 ==="
	sed -i 's/^MAIL_MAILER=.*/MAIL_MAILER=smtp/' src/.env
	sed -i 's/^MAIL_HOST=.*/MAIL_HOST=mailhog/' src/.env
	sed -i 's/^MAIL_PORT=.*/MAIL_PORT=1025/' src/.env
	sed -i 's/^MAIL_USERNAME=.*/MAIL_USERNAME=null/' src/.env
	sed -i 's/^MAIL_PASSWORD=.*/MAIL_PASSWORD=null/' src/.env
	sed -i 's/^MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=null/' src/.env
	sed -i 's/^MAIL_FROM_ADDRESS=.*/MAIL_FROM_ADDRESS=attendance@example.com/' src/.env
	sed -i 's/^MAIL_FROM_NAME=.*/MAIL_FROM_NAME="出席管理システム"/' src/.env

	@echo "=== ストレージディレクトリ作成 ==="
	@mkdir -p ./src/storage/app/public/img
	@mkdir -p ./src/storage/app/public/uploads

	@echo "=== アプリケーションキー生成 ==="
	docker-compose exec php php artisan key:generate

	@echo "=== ストレージリンク作成 ==="
	docker-compose exec php php artisan storage:link

	@echo "=== 権限設定 ==="
	docker-compose exec php chmod -R 777 storage bootstrap/cache

	@echo "=== マイグレーション実行 ==="
	docker-compose exec php php artisan migrate

	@echo "=== シーディング実行 ==="
	docker-compose exec php php artisan db:seed

	@echo "=== フロントエンド依存関係インストール ==="
	@make npm-install

	@echo "=== フロントエンドビルド ==="
	@make npm-build

	@echo "=== 開発環境初期化完了 ==="
	@echo "=== アクセス情報 ==="
	@echo "Web: http://localhost"
	@echo "phpMyAdmin: http://localhost:8080"
	@echo "MailHog: http://localhost:8025"

# データベースリフレッシュ
fresh:
	@echo "=== データベースリフレッシュ実行 ==="
	docker-compose exec php php artisan migrate:fresh --seed
	@echo "=== データベースリフレッシュ完了 ==="

# 再起動
restart:
	@echo "=== Docker環境再起動 ==="
	@make down
	@make up
	@echo "=== 再起動完了 ==="

# 起動
up:
	@echo "=== Docker環境起動 ==="
	docker-compose up -d
	@echo "=== 起動完了 ==="

# 停止
down:
	@echo "=== Docker環境停止 ==="
	docker-compose down --remove-orphans
	@echo "=== 停止完了 ==="

# キャッシュクリア
cache:
	@echo "=== キャッシュクリア実行 ==="
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php artisan config:clear
	docker-compose exec php php artisan route:clear
	docker-compose exec php php artisan view:clear
	docker-compose exec php php artisan config:cache
	@echo "=== キャッシュクリア完了 ==="

# コンテナ停止（データ保持）
stop:
	@echo "=== コンテナ停止 ==="
	docker-compose stop
	@echo "=== 停止完了 ==="

# ログ表示
logs:
	docker-compose logs -f

# PHPコンテナシェル接続
shell:
	docker-compose exec php bash

# MySQLシェル接続
mysql-shell:
	docker-compose exec mysql mysql -uroot -proot laravel_db

# テスト実行
test:
	@echo "=== テスト実行 ==="
	docker-compose exec php php artisan test
	@echo "=== テスト完了 ==="

# フロントエンド依存関係インストール
npm-install:
	@echo "=== npm依存関係インストール ==="
	docker-compose exec php npm install
	@echo "=== npmインストール完了 ==="

# フロントエンドビルド
npm-build:
	@echo "=== フロントエンドビルド実行 ==="
	docker-compose exec php npm run build
	@echo "=== ビルド完了 ==="

# 開発用ビルド（ウォッチ）
npm-dev:
	@echo "=== 開発用ビルド（ウォッチモード）開始 ==="
	docker-compose exec php npm run dev

# 完全クリーンアップ
clean:
	@echo "=== 完全クリーンアップ実行 ==="
	docker-compose down --remove-orphans --volumes
	docker system prune -f
	@echo "=== クリーンアップ完了 ==="

# Composer依存関係更新
composer-update:
	@echo "=== Composer依存関係更新 ==="
	docker-compose exec php composer update
	@echo "=== 更新完了 ==="

# Composer依存関係インストール
composer-install:
	@echo "=== Composer依存関係インストール ==="
	docker-compose exec php composer install
	@echo "=== インストール完了 ==="

# データベースバックアップ
backup:
	@echo "=== データベースバックアップ実行 ==="
	@mkdir -p backups
	docker-compose exec mysql mysqldump -uroot -proot laravel_db > backups/backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "=== バックアップ完了: backups/backup_$(shell date +%Y%m%d_%H%M%S).sql ==="

# ヘルプ
help:
	@echo "出席管理システム開発用Makefile"
	@echo ""
	@echo "使用可能なコマンド:"
	@echo "  init           : 開発環境の初期セットアップ"
	@echo "  fresh          : データベースリフレッシュ"
	@echo "  restart        : Docker環境再起動"
	@echo "  up             : Docker環境起動"
	@echo "  down           : Docker環境停止"
	@echo "  stop           : コンテナ停止（データ保持）"
	@echo "  cache          : Laravel キャッシュクリア"
	@echo "  logs           : ログ表示"
	@echo "  shell          : PHPコンテナシェル接続"
	@echo "  mysql-shell    : MySQLシェル接続"
	@echo "  test           : テスト実行"
	@echo "  npm-install    : フロントエンド依存関係インストール"
	@echo "  npm-build      : フロントエンドビルド"
	@echo "  npm-dev        : 開発用ビルド（ウォッチモード）"
	@echo "  composer-update: Composer依存関係更新"
	@echo "  composer-install: Composer依存関係インストール"
	@echo "  backup         : データベースバックアップ"
	@echo "  clean          : 完全クリーンアップ"
	@echo "  help           : このヘルプを表示"
	@echo ""
	@echo "初回セットアップ: make init"
	@echo "日常の開発: make up / make down"
