# Atte(勤怠管理システム)

メール認証・パスワード認証によりログインして、勤務時間・休憩時間を操作して管理できるようにしています。

**会員登録画面**<br />
FormRequestを使用してバリデーションをしております。<br />
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
翌日0時にリセットされるようになっています。<br />
ボタン処理の管理にはJavascriptを使用しています。
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

**Dockerビルド**
1. `git clone git@github.com:taienobutaka/AttendanceManegement.git`
2. DockerDesktopアプリを立ち上げる
3. `docker-compose up -d --build`

docker-compose.yml

``` bash
version: "3.8"

services:
  nginx:
    image: nginx:1.21.1
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
    image: mysql:8.0.26
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
**Laravel環境構築**
1. `docker-compose exec php bash`
2. `composer install`
3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.envファイルを作成
4. .envに以下の環境変数を追加
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

5. MailHogの設定
```
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="メールアドレス"
MAIL_FROM_NAME="${APP_NAME}"
```

6. アプリケーションキーの作成
``` bash
php artisan key:generate
```

7. マイグレーションの実行
``` bash
php artisan migrate
```

8. シーディングの実行
``` bash
php artisan db:seed
```

## 使用技術(実行環境)
- PHP 8.4.4
- Laravel 8.83.27
- MySQL 8.0.41
- Nginx 1.24.0
- JavaScript
- HTML5
- CSS3

## ER図
![スクリーンショット 2024-09-27 113511](https://github.com/user-attachments/assets/9fdbbb6a-0d28-40f2-9a6f-e25c4e64073c)

## 機能一覧
![スクリーンショット 2024-11-07 102701](https://github.com/user-attachments/assets/721acbe9-e691-41f1-9111-9134fe18f1c3)

## テーブル仕様
![スクリーンショット 2024-11-04 215120](https://github.com/user-attachments/assets/aca458f9-1b0e-4e88-9a4b-ef8e0b74ef80)

## 基本設計
![スクリーンショット 2024-11-08 093539](https://github.com/user-attachments/assets/b2122e26-4375-4510-bec8-e8bf0f229041)
![スクリーンショット 2024-11-08 093955](https://github.com/user-attachments/assets/ce485481-ff97-41d2-bd09-6a3874a5f7e1)

## ダミーデータ
```
名前  test
メールアドレス  test@gmail.com
パスワード  00000000
```

## URL
- 開発環境：http://localhost/
- phpMyAdmin:：http://localhost:8080/
- MailHog : http://localhost:8025
- 実行環境でメール認証を使用する際は、.envファイルの下記の設定を変更してください。
