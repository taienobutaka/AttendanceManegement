output "ec2_public_ip" {
  description = "EC2 パブリック IP（アプリの URL）"
  value       = var.use_elastic_ip ? aws_eip.app[0].public_ip : aws_instance.app.public_ip
}

output "app_url" {
  description = "アプリの URL（テスト用）"
  value       = "http://${var.use_elastic_ip ? aws_eip.app[0].public_ip : aws_instance.app.public_ip}"
}

output "rds_endpoint" {
  description = "RDS エンドポイント"
  value       = aws_db_instance.main.address
  sensitive   = false
}

output "rds_port" {
  value = aws_db_instance.main.port
}

# キーペアを Terraform で作成した場合のみ出力。GitHub Secrets の SSH_PRIVATE_KEY に登録すること
output "ec2_private_key_pem" {
  description = "EC2 秘密鍵（Terraform でキー作成した場合のみ）。GitHub Secrets に登録してデプロイに使用"
  value       = var.key_name == "" ? tls_private_key.ec2[0].private_key_pem : null
  sensitive   = true
}

output "ec2_key_name" {
  description = "EC2 キーペア名"
  value       = local.key_name
}

# GitHub OIDC 用ロール（Actions で Assume する ARN）
output "github_actions_role_arn" {
  description = "GitHub Actions が Assume する IAM ロール ARN"
  value       = aws_iam_role.github_actions.arn
}

output "deploy_ssm_prefix" {
  description = "デプロイ用 .env 生成に使う SSM パラメータのプレフィックス（deploy.yml の DEPLOY_SSM_PREFIX と一致させる）"
  value       = "/${var.project_name}/${var.environment}/deploy"
}

output "laravel_app_key" {
  description = "Laravel APP_KEY（user_data / SSM と同一）。SSM 未作成時は GitHub Secret LARAVEL_APP_KEY に登録"
  value       = local.app_key_base64
  sensitive   = true
}
