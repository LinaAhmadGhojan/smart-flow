#!/usr/bin/env python3
"""Deploy SmartFlow to Hostinger via SSH. Credentials via environment variables only.

Required:
  HOSTINGER_SSH_HOST
  HOSTINGER_SSH_PORT   (default 65002)
  HOSTINGER_SSH_USER
  HOSTINGER_SSH_PASS

Optional:
  HOSTINGER_SITE_PATH  (auto-detected if omitted)
  GIT_REPO             (default: origin remote)
  GIT_BRANCH           (default: main)

  pip install paramiko
  python scripts/deploy-hostinger.py
"""

import os
import sys

try:
    import paramiko
except ImportError:
    print("Install: pip install paramiko")
    sys.exit(1)

HOST = os.environ.get("HOSTINGER_SSH_HOST", "")
PORT = int(os.environ.get("HOSTINGER_SSH_PORT", "65002"))
USER = os.environ.get("HOSTINGER_SSH_USER", "")
PASSWORD = os.environ.get("HOSTINGER_SSH_PASS", "")
SITE_PATH = os.environ.get("HOSTINGER_SITE_PATH", "")
REPO = os.environ.get("GIT_REPO", "https://github.com/LinaAhmadGhojan/smart-flow.git")
BRANCH = os.environ.get("GIT_BRANCH", "main")

COMMANDS = f"""
set -e
if [ -n "{SITE_PATH}" ] && [ -f "{SITE_PATH}/artisan" ]; then
  cd "{SITE_PATH}"
else
  for d in ~/domains/smartflowuae.com/public_html ~/public_html; do
    if [ -f "$d/artisan" ]; then cd "$d"; break; fi
  done
fi
pwd
mkdir -p storage/framework/views storage/framework/sessions storage/framework/cache/data storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
git remote set-url origin {REPO} 2>/dev/null || true
git fetch origin
git pull origin {BRANCH}
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
echo DEPLOY_OK
"""


def main() -> None:
    missing = [k for k, v in {
        "HOSTINGER_SSH_HOST": HOST,
        "HOSTINGER_SSH_USER": USER,
        "HOSTINGER_SSH_PASS": PASSWORD,
    }.items() if not v]
    if missing:
        print("Set environment variables:", ", ".join(missing))
        sys.exit(1)

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    print(f"Connecting to {HOST}:{PORT}...")
    client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=30)

    stdin, stdout, stderr = client.exec_command(COMMANDS, get_pty=True)
    for line in stdout:
        print(line, end="")
    err = stderr.read().decode()
    if err:
        print(err, file=sys.stderr)
    code = stdout.channel.recv_exit_status()
    client.close()

    if code == 0:
        print("\nDone! Hard-refresh the site (Ctrl+F5).")
    else:
        print(f"\nDeploy failed (exit {code}).")
        sys.exit(code)


if __name__ == "__main__":
    main()
