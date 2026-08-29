#!/bin/bash
# Safe git pull on Hostinger when local files block the merge.
# Usage: bash scripts/hostinger-pull.sh
set -e
cd "$(dirname "$0")/.."

BACKUP_DIR="${HOME}/smartflow-backup-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"

echo "==> Backing up local files to $BACKUP_DIR"
for f in .htaccess .gitignore company-info.json public/company-info.json; do
  if [ -f "$f" ]; then
    cp "$f" "$BACKUP_DIR/"
  fi
done

echo "==> Resetting files that usually block git pull on Hostinger"
git checkout -- .htaccess .gitignore public/company-info.json company-info.json 2>/dev/null || true

echo "==> Pulling latest code from GitHub"
git pull origin main

echo "==> Running deploy"
bash deploy.sh

echo "==> Done. Verify:"
echo "    curl -s https://smartflowuae.com/build/manifest.json | grep main"
echo "    curl -s https://smartflowuae.com/api/settings | grep -E 'logo|signature'"
