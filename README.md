# Atte(勤怠管理システム)

メール認証・パスワード認証によりログインして、勤務時間・休憩時間を操作して管理できるようにしています。

**会員登録画面**<br />
FormRequest を使用してバリデーションをしております。<br />
会員登録ボタンをクリックすると、認証メールが送信されます。
![alt text](<スクリーンショット 2025-03-12 133614.png>)

**認証メール**<br />
送信されたメールの`勤怠入力画面`をクリックするとログイン画面に移動します。
![alt text](<スクリーンショット 2025-03-12 140903.png>)

**ログイン画面**<br />
会員登録と同様にバリデーションしております。
![alt text](<スクリーンショット 2025-03-12 133632.png>)

**勤怠入力画面**<br />
中央の４つのボタンで勤務時間、休憩時間を管理します。<br />
初めの画面では、`勤務開始`しかクリックできないようになっております。<br />
![スクリーンショット 2024-11-03 222912](https://github.com/user-attachments/assets/ca0aeab2-88d5-4ab8-9fc8-34537c9a5624)
`勤務開始`をクリックすると、`勤務終了`・`休憩開始`が有効となり、<br />
`勤務開始`・`休憩終了`が無効状態になります。
![alt text](<スクリーンショット 2025-03-12 134041.png>)
`休憩開始`をクリックすると、`休憩終了`が有効となり、<br />
`勤務開始`・`勤務終了`・`休憩開始`が無効になります。
![alt text](<スクリーンショット 2025-03-12 134125.png>)
`休憩終了`をクリックすると、`勤務終了`・`休憩開始`が有効となり、<br />
`勤務開始`・`休憩終了`が無効状態になります。
![alt text](<スクリーンショット 2025-03-12 134137.png>)
`勤務終了`をクリックすると、全てのボタンが無効となり、<br />
翌日 0 時にリセットされるようになっています。<br />
ボタン処理の管理には Javascript を使用しています。
![alt text](<スクリーンショット 2025-03-12 134207.png>)

**会員一覧画面**<br />
登録している会員と最新勤務日が、ページネーションにより
一覧で表示しています。
![スクリーンショット 2024-11-07 092546](https://github.com/user-attachments/assets/123d3d2d-a072-438d-b6b1-219e091703c7)

**ユーザー詳細画面**<br />
青色の`詳細`をクリックすると、勤務時間と休憩時間が表示されます。
![スクリーンショット 2024-11-07 092605](https://github.com/user-attachments/assets/760d72e3-f9da-4d64-a5f4-355464723ebe)

**日付一覧画面**<br />
日付ごとに、一覧表示できるようにしています。
![スクリーンショット 2024-11-07 092639](https://github.com/user-attachments/assets/98d8723a-09ab-4488-8e24-79f86ab0e02b)

## 環境構築

### 🚀 簡単セットアップ

Makefile を使用した自動セットアップが利用可能です。

1. **リポジトリのクローン**

   ```bash
   git clone git@github.com:taienobutaka/AttendanceManegement.git
   cd AttendanceManegement
   ```

2. **DockerDesktop アプリを立ち上げる**

3. **自動セットアップ実行**

   ```bash
   make init
   ```

   このコマンドで以下が自動実行されます：

   - Docker コンテナのビルドと起動
   - MySQL 接続待機
   - PHP 依存関係の更新・インストール
   - .env ファイルの作成と設定
   - MailHog 設定の自動構成
   - ストレージディレクトリ作成
   - アプリケーションキー生成
   - マイグレーション実行
   - シーディング実行
   - フロントエンドビルド

4. **アクセス確認**
   - **Web**: http://localhost
   - **phpMyAdmin**: http://localhost:8080
   - **MailHog**: http://localhost:8025

### 📋 Makefile コマンド一覧

| コマンド           | 説明                     |
| ------------------ | ------------------------ |
| `make init`        | **初回セットアップ**     |
| `make up`          | Docker 環境起動          |
| `make down`        | Docker 環境停止          |
| `make restart`     | Docker 環境再起動        |
| `make fresh`       | データベースリフレッシュ |
| `make cache`       | Laravel キャッシュクリア |
| `make test`        | **PHPUnit テスト実行**   |
| `make logs`        | ログ表示                 |
| `make shell`       | PHP コンテナシェル接続   |
| `make mysql-shell` | MySQL シェル接続         |
| `make backup`      | データベースバックアップ |
| `make help`        | 全コマンド表示           |

### 🔧 手動セットアップ

**Docker ビルド**

1. `git clone git@github.com:taienobutaka/AttendanceManegement.git`
2. DockerDesktop アプリを立ち上げる
3. `docker-compose up -d --build`

**docker-compose.yml**

```yaml
services:
  nginx:
    image: nginx:1.24.0
    ports:
      - "80:80"
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - ./src:/var/www/
    depends_on:
      - php

  php:
    build: ./docker/php
    volumes:
      - ./src:/var/www/

  mysql:
    image: mysql:8.0.41
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: laravel_db
      MYSQL_USER: laravel_user
      MYSQL_PASSWORD: laravel_pass
    command: mysqld --default-authentication-plugin=mysql_native_password
    volumes:
      - ./docker/mysql/data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    environment:
      - PMA_ARBITRARY=1
      - PMA_HOST=mysql
      - PMA_USER=laravel_user
      - PMA_PASSWORD=laravel_pass
    depends_on:
      - mysql
    ports:
      - 8080:80

  mailhog:
    image: mailhog/mailhog
    ports:
      - "1025:1025"
      - "8025:8025"
```

**Laravel 環境構築**

1. `docker-compose exec php bash`
2. 依存関係を更新・インストール
   ```bash
   composer update
   composer install
   ```
3. .env ファイルの作成と設定
   ```bash
   cp .env.example .env
   ```
4. .env ファイルのデータベース設定

   ```text
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=laravel_db
   DB_USERNAME=laravel_user
   DB_PASSWORD=laravel_pass
   ```

5. MailHog の設定

   ```text
   MAIL_MAILER=smtp
   MAIL_HOST=mailhog
   MAIL_PORT=1025
   MAIL_USERNAME=null
   MAIL_PASSWORD=null
   MAIL_ENCRYPTION=null
   MAIL_FROM_ADDRESS=attendance@example.com
   MAIL_FROM_NAME="出席管理システム"
   ```

6. アプリケーションキーの作成

   ```bash
   php artisan key:generate
   ```

7. ストレージリンク作成

   ```bash
   php artisan storage:link
   ```

8. マイグレーションの実行

   ```bash
   php artisan migrate
   ```

9. シーディングの実行

   ```bash
   php artisan db:seed
   ```

10. フロントエンドビルド
    ```bash
    npm install
    npm run production
    ```

## 使用技術(実行環境)

- PHP 8.4.4
- Laravel 8.83.27
- MySQL 8.0.41
- Nginx 1.24.0
- JavaScript / TypeScript
- TailwindCSS
- HTML5
- CSS3
- Docker & Docker Compose
- MailHog (開発用メールテスト)

## 開発ツール

- **Makefile**: 開発環境の自動セットアップ
- **Laravel Mix**: フロントエンドアセットのビルド
- **PHPUnit**: 単体テスト・機能テスト
- **Factory**: テストデータ生成
- **RefreshDatabase**: テスト用データベース管理
- **phpMyAdmin**: データベース管理

## ER 図

![スクリーンショット 2024-09-27 113511](https://github.com/user-attachments/assets/9fdbbb6a-0d28-40f2-9a6f-e25c4e64073c)

## 機能一覧

![スクリーンショット 2024-11-07 102701](https://github.com/user-attachments/assets/721acbe9-e691-41f1-9111-9134fe18f1c3)

## テーブル仕様

![スクリーンショット 2024-11-04 215120](https://github.com/user-attachments/assets/aca458f9-1b0e-4e88-9a4b-ef8e0b74ef80)

## 基本設計

![スクリーンショット 2024-11-08 093539](https://github.com/user-attachments/assets/b2122e26-4375-4510-bec8-e8bf0f229041)
![スクリーンショット 2024-11-08 093955](https://github.com/user-attachments/assets/ce485481-ff97-41d2-bd09-6a3874a5f7e1)

## 単体テスト

### 🧪 テスト概要

PHPUnit を使用して、テスト用の.env ファイルとデータベースを準備してアクセス・データベースなどの単体テストを行いました。

### テスト実行方法

```bash
# 基本のテスト実行
make test

# または直接実行
docker-compose exec php php artisan test

# より詳細な出力
docker-compose exec php php artisan test --verbose

# 特定のテストクラスを実行
docker-compose exec php php artisan test --filter=AccessTest

# カバレッジレポート付きで実行
docker-compose exec php php artisan test --coverage
```

### 実装済みテスト

#### Feature Tests (機能テスト)

- **AccessTest** - ページアクセステスト

  - ホームページアクセス（リダイレクト確認）
  - ログインページアクセス
  - 会員登録ページアクセス
  - 勤怠一覧ページアクセス

- **AttendanceTest** - 勤怠機能テスト

  - 勤怠データの作成・保存

- **RestTest** - 休憩機能テスト

  - 休憩データの作成・保存

- **UserTest** - ユーザー機能テスト

  - ユーザーデータの作成・保存

- **TestTest** - テスト機能テスト
  - テストデータの作成・保存

#### Unit Tests (単体テスト)

- **ExampleTest** - 基本動作確認テスト

### テスト結果

最新のテスト実行結果：

```
✓ Tests\Unit\ExampleTest
✓ Tests\Feature\AccessTest (4 tests)
✓ Tests\Feature\AttendanceTest
✓ Tests\Feature\RestTest
✓ Tests\Feature\TestTest
✓ Tests\Feature\UserTest

Tests: 10 passed
Time: ~1.00s
```

### テストデータベース

テストは`RefreshDatabase`トレイトを使用して、各テスト実行時にデータベースをリフレッシュし、テスト用ファクトリーでダミーデータを生成します。

## ダミーデータ

Seeder ファイルに２０人分のダミーデータが入っております。

## URL

- **開発環境**: http://localhost/
- **phpMyAdmin**: http://localhost:8080/
- **MailHog**: http://localhost:8025

> 💡 **ヒント**: 実行環境でメール認証を使用する際は、.env ファイルのメール設定を本番環境用に変更してください。

## 開発者向け情報

### 日常の開発フロー

```bash
# 開発開始
make up

# コードの変更...

# テスト実行（新機能追加時）
make test

# キャッシュクリア（設定変更時）
make cache

# 開発終了
make down
```

### テスト駆動開発

```bash
# テスト先行開発の場合
make test                    # 既存テストの確認
# 新しいテストを作成...
make test                    # 失敗することを確認
# 実装...
make test                    # テストが通ることを確認

# 継続的テスト
make shell                   # PHPコンテナに入る
php artisan test --filter=新機能名  # 特定機能のテスト
```

### トラブルシューティング

```bash
# ログ確認
make logs

# PHPコンテナに接続してデバッグ
make shell

# データベースに直接接続
make mysql-shell

# 完全リセット
make clean
make init
```
