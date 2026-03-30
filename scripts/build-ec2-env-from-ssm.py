#!/usr/bin/env python3
"""
EC2 用 Laravel .env を生成する。
1) SSM（/prefix/deploy/*）があれば優先
2) なければ RDS Describe + GitHub Secrets（フォールバック）
"""
from __future__ import annotations

import argparse
import json
import os
import subprocess
import sys


def _region() -> str:
    return os.environ.get("AWS_REGION", "ap-northeast-1")


def aws_ssm_get_optional(name: str, *, decrypt: bool) -> str | None:
    cmd = [
        "aws",
        "ssm",
        "get-parameter",
        "--name",
        name,
        "--region",
        _region(),
        "--query",
        "Parameter.Value",
        "--output",
        "text",
    ]
    if decrypt:
        cmd.append("--with-decryption")
    r = subprocess.run(cmd, text=True, capture_output=True)
    if r.returncode != 0 and "ParameterNotFound" in (r.stderr or ""):
        return None
    if r.returncode != 0:
        sys.stderr.write(r.stderr or "")
        r.check_returncode()
    return (r.stdout or "").strip()


def aws_ssm_get(name: str, *, decrypt: bool) -> str:
    v = aws_ssm_get_optional(name, decrypt=decrypt)
    if v is None:
        raise RuntimeError(f"SSM parameter missing unexpectedly: {name}")
    return v


def load_from_ssm(prefix: str) -> dict[str, str]:
    p = prefix.rstrip("/")
    host = aws_ssm_get_optional(f"{p}/rds_host", decrypt=False)
    if host is None:
        return {}
    return {
        "rds_host": host,
        "db_name": aws_ssm_get(f"{p}/db_name", decrypt=False),
        "db_username": aws_ssm_get(f"{p}/db_username", decrypt=False),
        "db_password": aws_ssm_get(f"{p}/db_password", decrypt=True),
        "laravel_app_key": aws_ssm_get(f"{p}/laravel_app_key", decrypt=True),
    }


def load_from_rds_fallback() -> dict[str, str]:
    instance_id = os.environ.get("DEPLOY_RDS_INSTANCE_ID", "atte-test").strip()
    password = os.environ.get("DEPLOY_DB_PASSWORD", "").strip()
    app_key = os.environ.get("DEPLOY_LARAVEL_APP_KEY", "").strip()

    if not password or not app_key:
        print(
            "::error::SSM に /…/deploy/* がありません。次のいずれかを実施してください: "
            "(1) terraform apply で SSM パラメータを作成する "
            "(2) GitHub Secrets に TF_VAR_DB_PASSWORD（RDS パスワード）と "
            "LARAVEL_APP_KEY（terraform output -raw laravel_app_key）を設定する",
            file=sys.stderr,
        )
        sys.exit(1)

    cmd = [
        "aws",
        "rds",
        "describe-db-instances",
        "--db-instance-identifier",
        instance_id,
        "--region",
        _region(),
        "--output",
        "json",
    ]
    out = subprocess.check_output(cmd, text=True)
    data = json.loads(out)
    instances = data.get("DBInstances") or []
    if not instances:
        print(f"::error::RDS インスタンスが見つかりません: {instance_id}", file=sys.stderr)
        sys.exit(1)
    inst = instances[0]
    endpoint = inst.get("Endpoint") or {}
    host = endpoint.get("Address")
    if not host:
        print("::error::RDS Endpoint.Address が取得できません", file=sys.stderr)
        sys.exit(1)
    db_name = inst.get("DBName") or ""
    if not db_name:
        print("::error::RDS DBName が空です（Terraform の db_name を確認）", file=sys.stderr)
        sys.exit(1)
    username = inst.get("MasterUsername") or ""
    if not username:
        print("::error::RDS MasterUsername が取得できません", file=sys.stderr)
        sys.exit(1)

    return {
        "rds_host": host,
        "db_name": db_name,
        "db_username": username,
        "db_password": password,
        "laravel_app_key": app_key,
    }


def dotenv_quote(value: str) -> str:
    escaped = value.replace("\\", "\\\\").replace('"', '\\"')
    return f'"{escaped}"'


def inject_openai_api_key(prefix: str, vals: dict[str, str]) -> None:
    """SSM の openai_api_key、なければ環境変数 DEPLOY_OPENAI_API_KEY を .env 用に取り込む。"""
    if vals.get("openai_api_key"):
        return
    p = prefix.rstrip("/")
    o = aws_ssm_get_optional(f"{p}/openai_api_key", decrypt=True)
    if o:
        vals["openai_api_key"] = o
        return
    env_o = os.environ.get("DEPLOY_OPENAI_API_KEY", "").strip()
    if env_o:
        vals["openai_api_key"] = env_o


def write_env(
    out_path: str, vals: dict[str, str], app_url: str, source: str
) -> None:
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
    if vals.get("openai_api_key"):
        lines.extend(
            ["", f"OPENAI_API_KEY={dotenv_quote(vals['openai_api_key'])}"]
        )
    out = "\n".join(lines) + "\n"
    out_abs = os.path.abspath(out_path)
    out_dir = os.path.dirname(out_abs)
    if out_dir:
        os.makedirs(out_dir, exist_ok=True)
    with open(out_path, "w", encoding="utf-8") as f:
        f.write(out)
    print(
        json.dumps(
            {"ok": True, "out_path": out_path, "bytes": len(out), "source": source}
        )
    )


def main() -> int:
    p = argparse.ArgumentParser()
    p.add_argument("prefix", help="例: /atte/test/deploy")
    p.add_argument("out_path", help="出力 .env")
    p.add_argument("--app-url", default="")
    args = p.parse_args()

    vals = load_from_ssm(args.prefix)
    if vals:
        source = "ssm"
    else:
        vals = load_from_rds_fallback()
        source = "rds_fallback"

    app_url = args.app_url or "http://PLACEHOLDER_UPDATE_AFTER_DEPLOY"
    inject_openai_api_key(args.prefix, vals)
    write_env(args.out_path, vals, app_url, source)
    return 0


if __name__ == "__main__":
    sys.exit(main())
