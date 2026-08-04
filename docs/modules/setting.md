# Settings Management Service - README

## 📋 Ümumi Məlumat

Laravel 12 əsaslı mərkəzləşdirilmiş parametr idarəetmə sistemi. Saytın bütün konfiqurasiyalarını JSON formatında saxlayır və cache vasitəsilə yüksək performans təmin edir.

### Əsas məqsəd:
- Sayt parametrlərinin mərkəzləşdirilmiş idarəsi
- Database-də saxlanan konfiqurasiyaların runtime-da yüklənməsi
- Cache mexanizmi ilə performans optimizasiyası
- Admin paneldən asanlıqla dəyişdirmə imkanı

## 🎯 Əsas Xüsusiyyətlər

- ✅ **JSON əsaslı saxlama** - Bütün parametrlər JSON formatında
- ✅ **Runtime konfiqurasiya** - Laravel konfiqurasiyalarının avtomatik yenilənməsi
- ✅ **Cache sistemi** - 24 saat cache ilə sürətli giriş
- ✅ **Şəkil idarəetməsi** - Logo, favicon və digər şəkillər
- ✅ **Multi-service dəstək** - Mail, Social, SEO, Security parametrləri
- ✅ **Auto seeding** - İlkin parametrlərin avtomatik yaradılması

## 🏗️ Fayl Strukturu

```
app/
├── Services/Module/SettingService.php           # Əsas biznes məntiq
├── Repositories/Module/SettingRepository.php    # Database əməliyyatları
├── Models/Setting.php                           # Setting model
├── Http/Controllers/Api/Admin/SettingController.php # Admin API
├── Services/App/System/ConfigurationLoaderService.php # Konfiq yükləyici
├── Providers/SettingServiceProvider.php         # Service provider
└── Services/Filter/SettingFilter.php            # Filter sistemi

database/
├── migrations/create_settings_table.php         # Database strukturu
└── seeders/SettingSeeder.php                   # İlkin parametrlər
```

## 📊 Database Strukturu

### Settings Table
```sql
CREATE TABLE settings (
    id BIGINT PRIMARY KEY,
    key VARCHAR(255) UNIQUE,        # Parametr açarı (info, mail, seo)
    values JSON,                    # Bütün dəyərlər JSON formatında
    created_by BIGINT,              # Kim yaradıb
    updated_by BIGINT,              # Kim yeniləyib
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP            # Soft delete
);
```

### JSON Strukturu Nümunəsi
```json
{
    "translates": {
        "az": {
            "name": "Mənim Saytım",
            "description": "Sayt haqqında məlumat"
        },
        "en": {
            "name": "My Website",
            "description": "About the website"
        }
    },
    "logo": "logo.svg",
    "email": "info@example.com",
    "phone": [
        {
            "number": "+994501234567",
            "is_whatsapp": true
        }
    ]
}
```

## 🔧 Service Metodları

### SettingService
```php
// Bütün parametrləri əldə etmək
all()                                    # Cache-dən bütün parametrlər

// Konkret parametr əldə etmək  
get(string $key, ?string $path = null, $default = null)

// Parametr təyin etmək
set(string $key, $value, ?string $path = null)

// Cache təmizləmək
clearSettingsCache()

// Export/Import
export(): array                         # Bütün parametrləri export
import(array $settings): void           # Parametrləri import
```

### İstifadə nümunələri:
```php
$settingService = app(SettingService::class);

// Sayt adını almaq
$siteName = $settingService->get('info', 'translates.az.name');

// Mail host dəyişmək
$settingService->set('mail', 'smtp.gmail.com', 'host');

// Bütün sosial media parametrləri
$socialMedia = $settingService->get('socialMedia');
```

## 🔗 API Endpoints

### Admin Panel
```http
GET    /api/admin/settings              # Bütün parametrlərin siyahısı
GET    /api/admin/settings/{key}        # Konkret parametr qrupu
PUT    /api/admin/settings/{key}        # Parametr qrupunu yeniləmə
```

### Nümunə API çağırışları:

#### Sayt məlumatlarını əldə etmək
```bash
curl -X GET "/api/admin/settings/info" \
-H "Authorization: Bearer TOKEN"
```

#### Mail parametrlərini yeniləmək
```bash
curl -X PUT "/api/admin/settings/mail" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "values": {
        "driver": "smtp",
        "host": "smtp.gmail.com",
        "port": 587,
        "username": "user@gmail.com",
        "password": "password",
        "encryption": "tls"
    }
}'
```

#### Şəkil yükləmək (logo)
```bash
curl -X PUT "/api/admin/settings/info" \
-H "Authorization: Bearer TOKEN" \
-F "logo=@/path/to/logo.png" \
-F "values[email]=info@example.com"
```

## 📋 Parametr Qrupları

### 1. Site Info (info)
Saytın əsas məlumatları:
```json
{
    "translates": { "az": {...}, "en": {...} },
    "logo": "logo.svg",
    "logo_dark": "logo_dark.svg", 
    "mobile_logo": "mobile_logo.svg",
    "favicon": "favicon.png",
    "email": "info@example.com",
    "phone": [{"number": "+994501234567", "is_whatsapp": true}],
    "working_hours": {"monday": "09:00 - 18:00", ...}
}
```

### 2. Mail Settings (mail)
Email konfiqurasiyaları:
```json
{
    "driver": "smtp",
    "host": "smtp.gmail.com",
    "port": 587,
    "encryption": "tls",
    "username": "user@gmail.com",
    "password": "password",
    "from_address": "noreply@example.com",
    "from_name": "My Website"
}
```

### 3. Social Media (socialMedia)
Sosial şəbəkə linkləri:
```json
{
    "facebook": {
        "url": "https://facebook.com/mywebsite",
        "icon": "fab fa-facebook",
        "active": true
    },
    "instagram": {...},
    "twitter": {...}
}
```

### 4. SEO Settings (seo)
SEO və analitika parametrləri:
```json
{
    "google": {
        "analytics_id": "UA-123456-1",
        "tag_manager": "GTM-ABCDEF",
        "search_console_key": "google-site-verification=abc123"
    },
    "facebook": {
        "pixel": "123456789012345"
    },
    "robots_txt": "User-agent: *\nDisallow: /admin/"
}
```

### 5. Upload Settings (upload)
Fayl yükləmə parametrləri:
```json
{
    "max_file_size": 10240,
    "allowed_file_types": {
        "image": ["jpg", "jpeg", "png", "webp"],
        "document": ["pdf", "doc", "docx"]
    },
    "image_quality": 85,
    "watermark": {
        "enabled": false,
        "position": "bottom-right"
    }
}
```

### 6. Security Settings (security)
Təhlükəsizlik parametrləri:
```json
{
    "max_login_attempts": 5,
    "login_lockout_time": 15,
    "password_policy": {
        "min_length": 8,
        "require_uppercase": true,
        "require_numeric": true
    },
    "api_rate_limit": {
        "enabled": true,
        "max_attempts": 60
    }
}
```

## 🌱 Seeding Sistemi

### SettingSeeder
Sistem ilk dəfə qurulduqda avtomatik parametrləri yaradır:

```bash
php artisan db:seed --class=SettingSeeder
```

**Yaradılan parametr qrupları:**
- Site məlumatları (logo, ad, əlaqə)
- Mail konfiqurasiyaları
- Sosial media linkləri
- SEO və analitika kodları
- Yükləmə parametrləri
- Təhlükəsizlik qaydaları
- Sistem parametrləri

## ⚙️ Konfiqurasiya Yükləmə Sistemi

### ConfigurationLoaderService
Bu servis database-dəki parametrləri Laravel konfiqurasiyalarına çevirir:

```php
// Mail parametrlərini Laravel mail konfiqurasiyasına yükləyir
'mail.mailers.smtp.host' => setting('mail.host')
'mail.mailers.smtp.port' => setting('mail.port')

// App parametrləri
'app.name' => setting('info.translates.az.name')
'app.timezone' => setting('system.timezone')

// Cache parametrləri
'cache.default' => setting('system.cache.driver')
```

### SettingServiceProvider
Application boot zamanı avtomatik parametrləri yükləyir:

**İşləmə prinsipi:**
1. Cache-dən konfiqurasiyaları yoxlayır
2. Cache yoxdursa, database-dən yükləyir
3. Laravel konfiqurasiyalarını yeniləyir
4. Cache-ə yazır (24 saat)

## 🚀 Performance Optimizasiyası

### Cache Strategiyası
```php
// Cache açarları
'app_settings'                    # Bütün parametrlər (24 saat)
'app_settings.info'              # Info parametrləri
'app_settings.mail.host'         # Konkret dəyər

// Cache təmizləmə
Artisan::call('repo:clear');     # Bütün repo cache
clearSettingsCache();           # Setting cache
```

### Database İndekslər
```sql
CREATE INDEX idx_settings_key ON settings(key);
CREATE INDEX idx_settings_created_at ON settings(created_at);
```

## 🛠 Helper Funksiyalar

### Global setting() helper
```php
// helpers.php faylında
function setting(string $key, $default = null) {
    return app(SettingService::class)->get($key, null, $default);
}

// Konkret path üçün
function setting(string $key, string $path, $default = null) {
    return app(SettingService::class)->get($key, $path, $default);
}
```

### İstifadə nümunələri:
```php
// Blade template-də
{{ setting('info.translates.az.name') }}
{{ setting('info.email') }}

// Controller-də
$siteName = setting('info', 'translates.'.app()->getLocale().'.name');
$mailHost = setting('mail', 'host');

// Config-də 
config('mail.mailers.smtp.host')  // Avtomatik yüklənir
```

## 🔧 Validation Rules

### Setting-ə görə validation:
```php
// Info parametrləri üçün
'values.email' => 'required|email'
'values.translates.az.name' => 'required|string|max:255'

// Mail parametrləri üçün  
'values.host' => 'required|string'
'values.port' => 'required|integer|min:1|max:65535'
'values.username' => 'required|string'

// SEO parametrləri üçün
'values.google.analytics_id' => 'nullable|string|regex:/^UA-\d+-\d+$/'
```

## 🔄 Runtime Konfiqurasiya Dəyişikliyi

Parametr dəyişdikdə avtomatik konfiqurasiya yeniləməsi:

```php
// Mail host dəyişdirmək
$settingService->set('mail', 'smtp.gmail.com', 'host');

// Laravel konfiqurasiyası avtomatik yenilənir
config('mail.mailers.smtp.host'); // smtp.gmail.com

// Cache təmizlənir və yeni dəyər yüklənir
```

## 🧪 Testing

### Unit Test Nümunəsi
```php
public function test_can_get_setting_value()
{
    Setting::create([
        'key' => 'test',
        'values' => ['name' => 'Test Site']
    ]);
    
    $value = $this->settingService->get('test', 'name');
    
    $this->assertEquals('Test Site', $value);
}

public function test_can_set_setting_value()
{
    $this->settingService->set('test', 'New Value', 'name');
    
    $value = $this->settingService->get('test', 'name');
    
    $this->assertEquals('New Value', $value);
}
```

### API Test
```php
public function test_admin_can_update_settings()
{
    $admin = User::factory()->admin()->create();
    
    $response = $this->actingAs($admin)
        ->putJson('/api/admin/settings/info', [
            'values' => ['email' => 'new@example.com']
        ]);
        
    $response->assertOk();
    $this->assertEquals('new@example.com', setting('info', 'email'));
}
```

## ⚠️ Xəta İdarəetməsi

### ConfigurationLoaderService xəta idarəsi:
- **Production-da**: Xətalar log edilir, default konfig istifadə olunur
- **Development-da**: Exception atılır
- **Console əmrlərində**: Konfiq yükləmə atlanır

### Cache problemi:
- Database table yoxdursa file cache-ə keçir
- Cache error-u olduqda database-dən birbaşa oxuyur

## 📋 İcazə Sistemi

**Tələb olunan icazələr:**
- `config_read` - Parametrləri oxuma
- `config_update` - Parametrləri yeniləmə

## 🔄 Yenilənmə Qeydləri

**v1.0.0** (2025-01-15)
- İlkin versiya
- JSON əsaslı parametr sistemi
- Runtime konfiqurasiya yükləmə
- Cache optimizasiyası
- Multi-service dəstək
