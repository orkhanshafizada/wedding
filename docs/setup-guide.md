# Laravel 12 Application Setup Guide

## 📋 Layihə Haqqında

Bu Laravel 12 framework-ü əsasında hazırlanmış web tətbiqinin quraşdırma və konfiqurasiya bələdçisidir.

## 🛠 Sistem Tələbləri

- **PHP**: 8.2 və ya daha yeni versiya
- **Composer**: Son versiya
- **Node.js & NPM**: Frontend assets üçün (əgər lazımdırsa)
- **MySQL/PostgreSQL**: Məlumat bazası
- **Extensions**: DOM, Fileinfo, LibXML

## 📦 Quraşdırma Addımları

### 1. Repository-ni klonlayın

```bash
git clone <repository-url>
cd <project-name>
```

### 2. Composer paketlərini quraşdırın

```bash
composer install
```

### 3. Environment faylını hazırlayın

```bash
cp .env.example .env
```

### 4. Application key yaradın

```bash
php artisan key:generate
```

### 5. Məlumat bazasını konfiqurasiya edin

`.env` faylını redaktə edin:

```env
APP_NAME="Your App Name"
APP_ENV=local
APP_KEY=base64:generated_key_here
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Məlumat bazasını yaradın

Əgər SQLite istifadə edirsinizsə:
```bash
touch database/database.sqlite
```

MySQL/PostgreSQL üçün məlumat bazasını əl ilə yaradın.

### 7. Migration-ları işə salın

```bash
php artisan migrate
```

### 8. Storage linkini yaradın

```bash
php artisan storage:link
```

### 9. Cache-ləri təmizləyin

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 10. Serveri işə salın

```bash
php artisan serve
```

Layihə `http://localhost:8000` ünvanında əlçatan olacaq.

## 🔧 Quraşdırılmış Paketlər

### Production Paketləri

- **Laravel Framework** (^12.0) - Əsas framework
- **Laravel Sanctum** (^4.0) - API authentication
- **Laravel Socialite** (^5.17) - Sosial media login
- **Intervention Image** (^3.8) - Şəkil emalı
- **Maatwebsite Excel** (^3.1) - Excel import/export
- **Jenssegers Agent** (^2.6) - Browser/device detection
- **Laravel Enum** (^6.11) - Enum dəstəyi

### Development Paketləri

- **Laravel Telescope** (^5.3) - Debug və monitoring
- **Debugbar** (^3.14) - Development debug bar
- **Swagger PHP** (^4.11) - API dokumentasiya
- **Laravel Pint** (^1.13) - Code formatting
- **PHPUnit** (^11.0.1) - Testing framework

## ⚙️ Əlavə Konfiqurasiyalar

### Sosial Login Konfiqurasiyası

`.env` faylına əlavə edin:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URL="${APP_URL}/auth/google/callback"

# Facebook OAuth (əgər lazımdırsa)
FACEBOOK_CLIENT_ID=your_facebook_client_id
FACEBOOK_CLIENT_SECRET=your_facebook_client_secret
FACEBOOK_REDIRECT_URL="${APP_URL}/auth/facebook/callback"
```

### Mail Konfiqurasiyası

```env
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Queue Konfiqurasiyası

```env
QUEUE_CONNECTION=database
# və ya
QUEUE_CONNECTION=redis
```

Queue worker işə salmaq:
```bash
php artisan queue:work
```

## 🔍 Development Tools

### Laravel Telescope

Development mühitində monitoring üçün:

```bash
php artisan telescope:install
php artisan migrate
```

`/telescope` URL-i ilə daxil olun.

### Code Style (Laravel Pint)

```bash
# Code style yoxlamaq
./vendor/bin/pint --test

# Code style düzəltmək
./vendor/bin/pint
```

## 🧪 Testing

```bash
# Bütün testləri işə salmaq
php artisan test

# Müəyyən test faylını işə salmaq
php artisan test tests/Feature/ExampleTest.php

# Coverage report
php artisan test --coverage
```

## 📁 Layihə Strukturu

```
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Helpers/
│   │   └── helpers.php
│   └── Enums/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   └── js/
├── routes/
│   ├── web.php
│   └── api.php
└── storage/
```

## 🚀 Production Deployment

### 1. Environment konfiqurasiyası

```env
APP_ENV=production
APP_DEBUG=false
```

### 2. Optimization

```bash
# Config cache
php artisan config:cache

# Route cache
php artisan route:cache

# View cache
php artisan view:cache

# Autoloader optimization
composer install --optimize-autoloader --no-dev
```

### 3. File permissions

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 🔒 Security

### Important Security Settings

```env
APP_DEBUG=false
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_SECURE_COOKIE=true
```

## ❗ Troubleshooting

### Ümumi Problemlər

**Vendor folder yoxdur:**
```bash
composer install
```

**Permission errors:**
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

**Class not found:**
```bash
composer dump-autoload
```

**Config cached:**
```bash
php artisan config:clear
```

## 📝 Development Workflow

1. `.env` faylını konfiqurasiya edin
2. Məlumat bazasını yaradın və migrate edin
3. Development serveri işə salın
4. Kodunuzu yazın və test edin
5. Pint ilə code style yoxlayın
6. PHPUnit testlərini işə salın

## 🤝 Team Development

### Git Hooks (tövsiyə edilir)

```bash
# pre-commit hook
#!/bin/sh
./vendor/bin/pint --test
php artisan test
```

### Code Review Checklist

- [ ] Pint code style keçir
- [ ] Testlər yazılıb və keçir
- [ ] Migration faylları düzgündür
- [ ] Security baxımından təhlükəsizdir

---

**Qeyd**: Bu setup bələdçisi Laravel 12 layihəsinin quraşdırma və konfiqurasiya prosesini əhatə edir. Hər hansı problem yaşasanız, log fayllarını yoxlayın: `storage/logs/laravel.log`
