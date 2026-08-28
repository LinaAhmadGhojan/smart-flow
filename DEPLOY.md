# رفع التحديثات على Hostinger — smartflowuae.com

## 1) على جهازك (تم ✓)

```bash
npm run build
git push origin cursor/migrate-to-laravel-vue
git push origin cursor/migrate-to-laravel-vue:master
```

> إذا `Repository not found`: سجّل دخول GitHub:
> ```bash
> gh auth login
> ```
> أو استخدم Personal Access Token.

---

## 2) على Hostinger — SSH

```bash
ssh -p 65002 u696702336@92.113.18.71
```

```bash
cd ~/domains/smartflowuae.com/public_html
# أو: cd ~/public_html

git pull origin master
bash deploy.sh
```

---

## 3) `.env` على السيرفر (مرة واحدة)

تأكد أن `.env` موجود على السيرفر (لم يُرفع مع Git):

```env
CHAT_AI_ENABLED=true
CHAT_AI_PROVIDER=groq
CHAT_AI_API_KEY=your_groq_key
CHAT_AI_MODEL=allam-2-7b
```

```bash
php artisan config:clear
```

---

## 4) تنظيف البيانات (اختياري — على السيرفر فقط)

```bash
php artisan db:purge-except-core --force
```

يحذف كل شيء **ما عدا**: products, reviews, admins.

---

## بديل: Git من لوحة Hostinger

**Websites → smartflowuae.com → Advanced → GIT**

- Repository: `https://github.com/eng-lina-ghojan/smartflow.git`
- Branch: `master`
- Deploy بعد كل push
