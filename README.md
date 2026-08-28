# SmartFlow - Laravel + Vue.js

مشروع SmartFlow متكامل مع Laravel (Backend) و Vue.js (Frontend)

## 📋 المتطلبات

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL أو PostgreSQL
- npm أو yarn

---

## 🚀 التشغيل السريع

### 1️⃣ تثبيت المكتبات

```powershell
# في مجلد المشروع
cd E:\smart

# تثبيت مكتبات Laravel
composer install

# تثبيت مكتبات Vue/Node
npm install
```

### 2️⃣ إعداد البيئة

```powershell
# نسخ ملف البيئة
copy .env.example .env

# توليد مفتاح التطبيق
php artisan key:generate

# إعداد قاعدة البيانات في .env:
# DB_DATABASE=smartflow
# DB_USERNAME=root
# DB_PASSWORD=
```

### 3️⃣ إعداد قاعدة البيانات

```powershell
# تشغيل Migrations
php artisan migrate

# إضافة البيانات الأساسية (Admin)
php artisan db:seed

# إنشاء رابط التخزين للصور
php artisan storage:link
```

### 4️⃣ تشغيل المشروع

**في نافذة PowerShell الأولى:**
```powershell
php artisan serve
```

**في نافذة PowerShell الثانية:**
```powershell
npm run dev
```

---

## 🌐 الروابط

- **الموقع:** http://localhost:5173
- **لوحة التحكم:** http://localhost:5173/admin
- **Laravel API:** http://localhost:8000

---

## 🔐 بيانات الدخول الافتراضية

- **البريد الإلكتروني:** `info@smartflow.ae`
- **كلمة السر:** ``

---

## 📁 هيكل المشروع

```
smart/
├── app/                      # Laravel Application
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php
│   │       ├── CategoryController.php
│   │       └── ProductController.php
│   └── Models/
│       ├── Admin.php
│       ├── Category.php
│       └── Product.php
│
├── database/
│   ├── migrations/          # Database Migrations
│   └── seeders/             # Database Seeders
│
├── routes/
│   ├── api.php              # API Routes
│   └── web.php              # Web Routes (Vue SPA)
│
├── resources/
│   ├── css/
│   │   └── app.css          # Tailwind CSS
│   ├── js/                  # Vue.js Application
│   │   ├── components/      # Vue Components
│   │   ├── views/           # Vue Pages
│   │   ├── router/          # Vue Router
│   │   ├── lib/             # API Client
│   │   ├── App.vue
│   │   └── main.ts
│   └── views/
│       └── app.blade.php    # Main Blade Template
│
├── public/                  # Public Assets
├── storage/                 # File Storage
├── .env.example             # Environment Template
├── composer.json            # PHP Dependencies
├── package.json             # Node Dependencies
├── vite.config.ts           # Vite Configuration
└── tailwind.config.js       # Tailwind Configuration
```

---

## 🔌 API Endpoints

### Authentication
- `POST /api/auth/login` - تسجيل الدخول
- `POST /api/auth/logout` - تسجيل الخروج
- `GET /api/auth/me` - معلومات المستخدم

### Products (عام)
- `GET /api/products` - جلب جميع المنتجات
- `GET /api/products/{id}` - جلب منتج واحد

### Products (محمي)
- `POST /api/products` - إضافة منتج
- `PUT /api/products/{id}` - تحديث منتج
- `DELETE /api/products/{id}` - حذف منتج
- `POST /api/products/upload` - رفع صورة

### Categories (عام)
- `GET /api/categories` - جلب جميع الفئات
- `GET /api/categories/{id}` - جلب فئة واحدة

### Categories (محمي)
- `POST /api/categories` - إضافة فئة
- `PUT /api/categories/{id}` - تحديث فئة
- `DELETE /api/categories/{id}` - حذف فئة

---

## 📦 البناء للإنتاج

```powershell
# تحسين Laravel
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# بناء Vue
npm run build
```

---

## 🛠️ التقنيات المستخدمة

### Backend
- Laravel 10
- Laravel Sanctum (Authentication)
- MySQL/PostgreSQL

### Frontend
- Vue 3 (Composition API)
- TypeScript
- Vue Router
- Pinia
- TanStack Query
- Tailwind CSS
- Vite

---

## 📝 أوامر مفيدة

```powershell
# تشغيل Migrations
php artisan migrate

# إعادة بناء قاعدة البيانات
php artisan migrate:fresh --seed

# مسح الـ Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# فحص الأخطاء
php artisan route:list
php artisan config:show database
```

---

## 🐛 حل المشاكل الشائعة

### المشكلة: الصور لا تظهر
```powershell
php artisan storage:link
```

### المشكلة: 401 Unauthorized
- تأكد من Token في sessionStorage
- تحقق من CORS في `config/cors.php`

### المشكلة: Vite connection refused
- تأكد من تشغيل `npm run dev`
- تحقق من أن المنفذ 5173 متاح

---

## 📞 الدعم

للمزيد من المعلومات أو المساعدة، يرجى التواصل مع فريق التطوير.

---

**© 2024 SmartFlow. All rights reserved.**
