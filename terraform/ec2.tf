# キーペア（Terraform で作成。秘密鍵は output で取得し GitHub Secrets に登録）
resource "tls_private_key" "ec2" {
  count     = var.key_name == "" ? 1 : 0
  algorithm = "RSA"
  rsa_bits  = 2048
}

resource "aws_key_pair" "generated" {
  count      = var.key_name == "" ? 1 : 0
  key_name   = "${var.project_name}-${var.environment}-key"
  public_key = tls_private_key.ec2[0].public_key_openssh
}

# Laravel APP_KEY: 「base64:」+ 32 バイト乱数の標準 Base64（php artisan key:generate と同形式）
# random_password の英数字 44 文字は Base64 デコード後 32 バイトにならず Encrypter が落ちるため使わない
resource "random_id" "laravel_app_key" {
  byte_length = 32
}

locals {
  app_key_base64 = "base64:${random_id.laravel_app_key.b64_std}"
  key_name       = var.key_name != "" ? var.key_name : aws_key_pair.generated[0].key_name
}

# EC2（無料枠: t3.micro, 8GB, パブリックサブネット）
resource "aws_instance" "app" {
  ami                    = data.aws_ami.amazon_linux_2023.id
  instance_type           = var.ec2_instance_type
  subnet_id               = aws_subnet.public.id
  vpc_security_group_ids  = [aws_security_group.ec2.id]
  key_name                = local.key_name
  root_block_device {
    volume_size = var.ec2_root_volume_size
    volume_type = "gp2"
  }

  user_data = templatefile("${path.module}/user_data.sh.tpl", {})

  lifecycle {
    ignore_changes = [user_data]
  }
}

# Elastic IP（オプション。上限に達している場合は use_elastic_ip = false）
resource "aws_eip" "app" {
  count    = var.use_elastic_ip ? 1 : 0
  domain   = "vpc"
  instance = aws_instance.app.id
}

# デプロイ用ワークフローが EC2 IP を取得するための SSM パラメータ
resource "aws_ssm_parameter" "ec2_ip" {
  name  = "/${var.project_name}/${var.environment}/ec2_public_ip"
  type  = "String"
  value = var.use_elastic_ip ? aws_eip.app[0].public_ip : aws_instance.app.public_ip
}
