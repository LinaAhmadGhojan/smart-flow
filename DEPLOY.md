# Deploy SmartFlow to Hostinger

## 1) Push code (local)

```bash
npm run build
# Set token only in your shell — never commit it
export GITHUB_TOKEN=ghp_xxxx
python scripts/push-to-github.ps1   # or: git push origin main
```

## 2) Pull on server (SSH)

Use credentials from **Hostinger → SSH Access** (do not store in repo).

```bash
ssh -p YOUR_PORT YOUR_USER@YOUR_HOST
cd ~/domains/smartflowuae.com/public_html
git pull origin main
bash deploy.sh
```

Or from your PC with env vars (no passwords in files):

```powershell
$env:HOSTINGER_SSH_HOST = "your-server-ip"
$env:HOSTINGER_SSH_PORT = "65002"
$env:HOSTINGER_SSH_USER = "your-ssh-user"
$env:HOSTINGER_SSH_PASS = "your-ssh-password"
python scripts/deploy-hostinger.py
```

## 3) Server `.env` (never in Git)

```env
CHAT_AI_ENABLED=true
CHAT_AI_API_KEY=your_groq_key
CHAT_AI_MODEL=allam-2-7b
```

```bash
php artisan config:clear
```

## Security

- Never commit `.env`, SSH passwords, or API keys.
- Rotate any credential that was shared in chat or logs.
- Keep `APP_DEBUG=false` on production.
