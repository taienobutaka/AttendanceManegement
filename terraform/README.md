# Terraform（AWS 無料枠・テスト用）

Laravel アプリ（Atte）の本番環境を AWS に構築します。EC2 1 台 + RDS MySQL 1 台の構成です。

## 前提

- AWS CLI 設定済み（`aws configure` または環境変数）
- Terraform >= 1.0 インストール済み

## 初回セットアップ

1. **tfvars の準備**

   ```bash
   cp terraform.tfvars.example terraform.tfvars
   # terraform.tfvars を編集し、github_repo と db_password を設定
   ```

2. **Terraform 実行（ローカルで 1 回）**

   ```bash
   cd terraform
   terraform init
   terraform plan -var="db_password=YOUR_RDS_PASSWORD"
   terraform apply -var="db_password=YOUR_RDS_PASSWORD"
   ```

3. **GitHub Secrets の登録**

   - `AWS_ROLE_ARN`: 出力の `github_actions_role_arn` をコピーして登録
   - `TF_VAR_DB_PASSWORD`: RDS マスターパスワード（Terraform で使う値と同じ）
   - `SSH_PRIVATE_KEY`: 出力の `ec2_private_key_pem`（Terraform でキー作成した場合）をそのまま登録

4. **アプリ URL**

   - 出力の `app_url`（例: `http://xx.xx.xx.xx`）でアクセス可能です（RDS 起動後、初回デプロイ後に表示されます）。

## 運用

- **Terraform**: `terraform/` やワークフロー変更を main に push すると GitHub Actions で plan が実行されます。Apply は手動（ワークフロー「Run workflow」で「Apply を実行する」にチェック）。
- **デプロイ**: `src/` を main に push すると GitHub Actions で EC2 にデプロイ（git pull → composer install → migrate → 再起動）されます。

## 無料枠の注意

- EC2 / RDS は 1 台ずつ。Elastic IP はデフォルト無効（`use_elastic_ip = false`）。上限に余裕があれば `terraform.tfvars` で `use_elastic_ip = true` にし、停止時は関連付けを外すこと。
- state はローカル保存。S3 に移す場合は `backend.tf` のコメントを有効化し、バケット・DynamoDB を事前作成してください。

## GitHub Actions でデプロイするまで

1. 上記の通り初回 `terraform apply` を実行し、以下を取得する。
2. **GitHub リポジトリの Settings → Secrets and variables → Actions** で次を登録する。
   - `AWS_ROLE_ARN`: `terraform output -raw github_actions_role_arn` の値
   - `SSH_PRIVATE_KEY`: `terraform output -raw ec2_private_key_pem` の値（改行含めてそのまま）
   - `TF_VAR_DB_PASSWORD`: `terraform.tfvars` の `db_password` と同じ値
3. **main ブランチに push** する。
   - `terraform/**` の変更 → Terraform ワークフローが plan（手動で Apply 可能）。
   - `src/**` の変更 → デプロイワークフローが EC2 に SSH して `git pull` → `composer install` → `migrate` → 再起動。
4. アプリの URL は `terraform output app_url`（例: `http://18.183.250.80`）。初回デプロイ後、数分で表示される。

**デプロイが失敗し「composer: command not found」と出る場合:** 表示している実行が古いためです。次のどちらかで**最新のワークフロー**でやり直してください。

- **手動実行（推奨）:** GitHub → **Actions** → 左の **「Deploy to EC2」** をクリック → 右側の **「Run workflow」** ドロップダウン → **「Run workflow」** ボタン。これで main の最新ワークフロー（Composer 自動インストール付き）が 1 回実行されます。
- **push で実行:** 新しいコミットを push すると新しい実行が起きます（例: `git commit --allow-empty -m "ci: trigger deploy" && git push origin main`）。
