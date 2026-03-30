variable "aws_region" {
  description = "AWS リージョン"
  type        = string
  default     = "ap-northeast-1"
}

variable "environment" {
  description = "環境名（テスト用）"
  type        = string
  default     = "test"
}

variable "project_name" {
  description = "プロジェクト名"
  type        = string
  default     = "atte"
}

# --- ネットワーク ---
variable "vpc_cidr" {
  description = "VPC CIDR"
  type        = string
  default     = "10.0.0.0/16"
}

variable "az" {
  description = "利用するアベイラビリティゾーン（EC2 用・1 AZ）"
  type        = string
  default     = "ap-northeast-1a"
}

variable "az_rds_2" {
  description = "RDS 用 DB サブネットグループの 2 つ目の AZ（AWS は最低 2 AZ を要求）"
  type        = string
  default     = "ap-northeast-1c"
}

variable "public_subnet_cidr" {
  description = "パブリックサブネット CIDR（EC2 用）"
  type        = string
  default     = "10.0.1.0/24"
}

variable "rds_subnet_cidr" {
  description = "RDS 用サブネット CIDR（1 AZ 目）"
  type        = string
  default     = "10.0.2.0/24"
}

variable "rds_subnet_cidr_2" {
  description = "RDS 用サブネット CIDR（2 AZ 目・サブネットグループ用）"
  type        = string
  default     = "10.0.3.0/24"
}

# --- EC2 ---
variable "ec2_instance_type" {
  description = "EC2 インスタンスタイプ（無料枠）"
  type        = string
  default     = "t3.micro"
}

variable "ec2_ami_owner" {
  description = "AMI オーナー（Amazon Linux 2023）"
  type        = string
  default     = "amazon"
}

variable "ec2_root_volume_size" {
  description = "EC2 ルートボリュームサイズ（GB）。Amazon Linux 2023 AMI は 30GB 以上必要"
  type        = number
  default     = 30
}

variable "use_elastic_ip" {
  description = "Elastic IP を割り当てるか（未使用時はインスタンスのパブリック IP を使用）"
  type        = bool
  default     = false
}

variable "github_repo" {
  description = "GitHub リポジトリ（例: taienobutaka/AttendanceManegement）"
  type        = string
}

variable "github_branch" {
  description = "デプロイ対象ブランチ"
  type        = string
  default     = "main"
}

# --- RDS ---
variable "db_name" {
  description = "RDS 初期データベース名"
  type        = string
  default     = "atte_production"
}

variable "db_username" {
  description = "RDS マスターユーザー名"
  type        = string
  default     = "admin"
}

variable "db_password" {
  description = "RDS マスターパスワード"
  type        = string
  sensitive   = true
}

variable "openai_api_key" {
  description = "OpenAI API キー（チャットボット）。空なら SSM パラメータは作成せず、手動で /…/deploy/openai_api_key を入れてもよい"
  type        = string
  default     = ""
  sensitive   = true
}

variable "db_instance_class" {
  description = "RDS インスタンスクラス（無料枠）"
  type        = string
  default     = "db.t4g.micro"
}

variable "db_allocated_storage" {
  description = "RDS ストレージ（GB）"
  type        = number
  default     = 20
}

# --- SSH（オプション: 既存キーペアを使う場合に指定）---
variable "key_name" {
  description = "既存の EC2 キーペア名。未指定なら Terraform で新規作成"
  type        = string
  default     = ""
}
