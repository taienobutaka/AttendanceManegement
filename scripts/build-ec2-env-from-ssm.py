#!/usr/bin/env python3
"""
SSM から RDS / APP_KEY を取得し、EC2 用の Laravel .env 1 ファイルを生成する。
GitHub Actions（ubuntu-latest）上で実行する想定。値に特殊文字が含まれても安全にクォートする。
"""
from __future__ import annotations

import argparse
import json
import os
import subprocess
import sys


def aws_ssm_get(name: str, *, decrypt: bool) -> str:
    cmd = [
        "aws",
        "ssm",
        "get-parameter",
        "--name",
        name,
        "--region",
        os.environ.get("AWS_REGION", "ap-northeast-1"),
        "--query",
        "Parameter.Value",
        "--output",
        "text",
    ]
    if decrypt:
        cmd.append("--with-decryption")
    out = subprocess.check_output(cmd, text=True)
    return out.strip()


def dotenv_quote(value: str) -> str:
    """Laravel .env 向けにダブルクォートでエスケープ。"""
    escaped = value.replace("\\", "\\\\").replace('"', '\\"')
    return f'"{escaped}"'


def main() -> int:
    p = argparse.ArgumentParser()
    p.add_argument("prefix", help='例: /atte/test/deploy （末尾スラッシュなし）')
    p.add_argument("out_path", help="出力 .env ファイルパス")
    p.add_argument(
        "--app-url",
        default="",
        help="APP_URL（空ならプレースホルダ。デプロイ後に EC2 のメタデータで上書き可）",
    )
    args = p.parse_args()
    prefix = args.prefix.rstrip("/")

    keys = {
        "rds_host": f"{prefix}/rds_host",
        "db_name": f"{prefix}/db_name",
        "db_username": f"{prefix}/db_username",
        "db_password": f"{prefix}/db_password",
        "laravel_app_key": f"{prefix}/laravel_app_key",
    }
    vals: dict[str, str] = {}
    for logical, param_name in keys.items():
        vals[logical] = aws_ssm_get(
            param_name, decrypt=logical in ("db_password", "laravel_app_key")
        )

    app_url = args.app_url or "http://PLACEHOLDER_UPDATE_AFTER_DEPLOY"

    lines = [
        "APP_NAME=Atte",
        "APP_ENV=production",
        "APP_DEBUG=false",
        f"APP_URL={dotenv_quote(app_url)}",
        f"APP_KEY={dotenv_quote(vals['laravel_app_key'])}",
        "",
        "LOG_CHANNEL=stack",
        "LOG_LEVEL=warning",
        "",
        "DB_CONNECTION=mysql",
        f"DB_HOST={dotenv_quote(vals['rds_host'])}",
        "DB_PORT=3306",
        f"DB_DATABASE={dotenv_quote(vals['db_name'])}",
        f"DB_USERNAME={dotenv_quote(vals['db_username'])}",
        f"DB_PASSWORD={dotenv_quote(vals['db_password'])}",
        "",
        "SESSION_DRIVER=file",
        "CACHE_DRIVER=file",
        "QUEUE_CONNECTION=sync",
    ]

    out = "\n".join(lines) + "\n"
    out_abs = os.path.abspath(args.out_path)
    out_dir = os.path.dirname(out_abs)
    if out_dir:
        os.makedirs(out_dir, exist_ok=True)
    with open(args.out_path, "w", encoding="utf-8") as f:
        f.write(out)

    # ログ用マスク（パスワードを出さない）
    print(json.dumps({"ok": True, "out_path": args.out_path, "bytes": len(out)}))
    return 0


if __name__ == "__main__":
    sys.exit(main())
