# Atte(勤怠管理システム)

メール認証・パスワード認証によりログインして、勤務時間・休憩時間を操作して管理できます。

![スクリーンショット 2024-11-07 092420](https://github.com/user-attachments/assets/d006565b-0802-4e5c-a675-04aa8da0e037)

![スクリーンショット 2024-11-07 092441](https://github.com/user-attachments/assets/43fe0c57-af22-496d-95e8-1a7f0a688bf4)

![スクリーンショット 2024-11-03 222912](https://github.com/user-attachments/assets/ca0aeab2-88d5-4ab8-9fc8-34537c9a5624)

![スクリーンショット 2024-11-07 092546](https://github.com/user-attachments/assets/123d3d2d-a072-438d-b6b1-219e091703c7)

![スクリーンショット 2024-11-07 092605](https://github.com/user-attachments/assets/760d72e3-f9da-4d64-a5f4-355464723ebe)

![スクリーンショット 2024-11-07 092639](https://github.com/user-attachments/assets/98d8723a-09ab-4488-8e24-79f86ab0e02b)

## 環境構築

**Dockerビルド**
1. `git clone git@github.com:taienobutaka/AttendanceManegement.git`
2. DockerDesktopアプリを立ち上げる
3. `docker-compose up -d --build`

``` bash
mysql:
    platform: linux/x86_64(この文追加)
    image: mysql:8.0.26
    environment:
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
5. アプリケーションキーの作成
``` bash
php artisan key:generate
```

6. マイグレーションの実行
``` bash
php artisan migrate
```

7. シーディングの実行
``` bash
php artisan db:seed
```

## 使用技術(実行環境)
- PHP8.3.0
- Laravel8.83.27
- MySQL8.0.26

## ER図
![スクリーンショット 2024-09-27 113511](https://github.com/user-attachments/assets/9fdbbb6a-0d28-40f2-9a6f-e25c4e64073c)

## 機能一覧
![スクリーンショット 2024-11-07 102701](https://github.com/user-attachments/assets/721acbe9-e691-41f1-9111-9134fe18f1c3)

## テーブル仕様
![スクリーンショット 2024-11-04 215120](https://github.com/user-attachments/assets/aca458f9-1b0e-4e88-9a4b-ef8e0b74ef80)

## 基本設計
![スクリーンショット 2024-11-08 093539](https://github.com/user-attachments/assets/b2122e26-4375-4510-bec8-e8bf0f229041)
![スクリーンショット 2024-11-08 093955](https://github.com/user-attachments/assets/ce485481-ff97-41d2-bd09-6a3874a5f7e1)

## URL
- 開発環境：http://localhost/
- phpMyAdmin:：http://localhost:8080/
- 実行環境でメール認証を使用する際は、.envファイルの下記の設定を変更してください。
