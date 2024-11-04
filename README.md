# Atte(勤怠管理システム)

メール認証・パスワードによりログインして、勤務時間・休憩時間を操作して管理できます。

![スクリーンショット 2024-11-03 222912](https://github.com/user-attachments/assets/ca0aeab2-88d5-4ab8-9fc8-34537c9a5624)

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

## ページ一覧
![スクリーンショット 2024-11-04 215047](https://github.com/user-attachments/assets/1835844d-5eda-4728-aefb-2afdfea23fbf)

## 機能一覧
![スクリーンショット 2024-11-04 215023](https://github.com/user-attachments/assets/c5e37944-3d5a-4978-8b15-cca73ed82fae)

## テーブル仕様
![スクリーンショット 2024-11-04 215120](https://github.com/user-attachments/assets/aca458f9-1b0e-4e88-9a4b-ef8e0b74ef80)

## 基本設計
![スクリーンショット 2024-11-04 215156](https://github.com/user-attachments/assets/e3afcd96-3aa2-4b37-9559-6ac02f5a64fa)
![スクリーンショット 2024-11-04 215222](https://github.com/user-attachments/assets/668caaab-1341-4766-a701-d2178cb74860)

## URL
- 開発環境：http://localhost/
- phpMyAdmin:：http://localhost:8080/
