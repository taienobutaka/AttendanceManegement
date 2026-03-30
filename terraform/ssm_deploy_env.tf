# デプロイ時に GitHub Actions が読み取り、EC2 の src/.env を生成するためのパラメータ
locals {
  deploy_ssm_prefix = "/${var.project_name}/${var.environment}/deploy"
}

resource "aws_ssm_parameter" "deploy_rds_host" {
  name        = "${local.deploy_ssm_prefix}/rds_host"
  description = "RDS endpoint for Laravel DB_HOST"
  type        = "String"
  value       = aws_db_instance.main.address
}

resource "aws_ssm_parameter" "deploy_db_name" {
  name        = "${local.deploy_ssm_prefix}/db_name"
  description = "RDS database name for Laravel DB_DATABASE"
  type        = "String"
  value       = var.db_name
}

resource "aws_ssm_parameter" "deploy_db_username" {
  name        = "${local.deploy_ssm_prefix}/db_username"
  description = "RDS username for Laravel DB_USERNAME"
  type        = "String"
  value       = var.db_username
}

resource "aws_ssm_parameter" "deploy_db_password" {
  name        = "${local.deploy_ssm_prefix}/db_password"
  description = "RDS password for Laravel DB_PASSWORD"
  type        = "SecureString"
  value       = var.db_password
}

resource "aws_ssm_parameter" "deploy_laravel_app_key" {
  name        = "${local.deploy_ssm_prefix}/laravel_app_key"
  description = "Laravel APP_KEY (must match user_data / session)"
  type        = "SecureString"
  value       = local.app_key_base64
}

# OpenAI（チャットボット）。空のときはパラメータを作らない（AWS CLI で手動投入可）
resource "aws_ssm_parameter" "deploy_openai_api_key" {
  count       = var.openai_api_key != "" ? 1 : 0
  name        = "${local.deploy_ssm_prefix}/openai_api_key"
  description = "OpenAI API key for Laravel OPENAI_API_KEY (chatbot)"
  type        = "SecureString"
  value       = var.openai_api_key
}
