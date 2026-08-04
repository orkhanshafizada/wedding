# Activity Log Service - README

## 📋 Ümumi Məlumat

Laravel 12 əsaslı hərtərəfli aktivlik izləmə sistemi. Sistemdəki bütün istifadəçi əməliyyatlarını, API çağırışlarını və dəyişiklikləri avtomatik qeyd edir. Audit trail və təhlükəsizlik monitorinqi üçün hazırlanmışdır.

### Əsas məqsəd:
- Bütün sistem əməliyyatlarının izlənməsi
- Dəyişikliklərin audit trail-ı
- API request/response logunun aparılması
- Təhlükəsizlik hadisələrinin monitorinqi

## 🎯 Əsas Xüsusiyyətlər

- ✅ **Hərtərəfli izləmə** - Model dəyişiklikləri, API çağırışları, login/logout
- ✅ **Avtomatik middleware** - API request-lər avtomatik loglanır
- ✅ **Həssas məlumat qoruma** - Şifrə və token-lar filtrlənir
- ✅ **Device tracking** - Browser, OS, cihaz məlumatları
- ✅ **Error logging** - Xətalar və status kodları
- ✅ **Audit trail** - Before/after dəyişikliklər
- ✅ **Statistics & reporting** - Analitik məlumatlar

## 🏗️ Fayl Strukturu

```
app/
├── Services/Module/ActivityLogService.php       # Əsas biznes məntiq
├── Repositories/Module/ActivityLogRepository.php # Database əməliyyatları
├── Models/ActivityLog.php                       # Log model və əlaqələr
├── Http/Controllers/Api/Admin/ActivityLogController.php # Admin API
├── Http/Middleware/ApiLoggingMiddleware.php     # API avtomatik log
├── Enums/
│   ├── ActivityLogActionEnum.php               # Əməliyyat növləri
│   └── ActivityLogStatusEnum.php               # Status növləri
└── Services/Filter/ActivityLogFilter.php       # Filter sistemi

database/
├── migrations/create_activity_logs_table.php   # Database strukturu
└── seeders/ActivityLogSeeder.php              # Test məlumatları
```

## 📊 Database Strukturu

### Activity Logs Table
```sql
CREATE TABLE activity_logs (
    id BIGINT PRIMARY KEY,
    uuid VARCHAR(36),
    
    -- Model məlumatları
    model_type VARCHAR(255),        # Model class (User, Setting)
    model_id BIGINT,               # Model ID
    
    -- Əməliyyat məlumatları
    action VARCHAR(255),           # created, updated, login
    old_data JSON,                 # Əvvəlki dəyərlər
    new_data JSON,                 # Yeni dəyərlər
    
    -- Request məlumatları
    ip_address VARCHAR(255),       # İstifadəçi IP
    user_agent TEXT,              # Browser məlumatları
    method VARCHAR(10),           # HTTP method
    url TEXT,                     # Request URL
    meta_data JSON,               # Əlavə məlumatlar
    
    -- Status
    status VARCHAR(50),           # success, error, pending
    error_message TEXT,           # Xəta mesajı
    
    created_by BIGINT,            # Kim əməliyyat edib
    updated_by BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

### JSON Strukturu Nümunələri

#### Model dəyişikliyi log:
```json
{
    "old_data": {
        "email": "old@example.com",
        "phone": "+994501111111"
    },
    "new_data": {
        "email": "new@example.com", 
        "phone": "+994502222222"
    },
    "meta_data": {
        "browser": "Chrome",
        "platform": "Windows",
        "device": "Desktop"
    }
}
```

#### API çağırış log:
```json
{
    "meta_data": {
        "duration_ms": 45.67,
        "request": {
            "headers": {...},
            "body": {...}
        },
        "response": {
            "status": 200,
            "body": {...}
        }
    }
}
```

## 🔧 Service Metodları

### ActivityLogService
```php
// Manual log yazmaq
log(string $action, ?Model $model = null, ?array $oldData = null, 
    ?array $newData = null, array $additionalData = []): void

// Xəta logu yazmaq
logError(string $action, string $message, ?Model $model = null): void

// Köhnə logları təmizləmək
cleanup(int $days = 30): int

// CRUD əməliyyatları (BaseCrudService-dən)
paginateAndFilter()                    # Filtrlənmiş siyahı
findById(int $id)                      # ID ilə tapmaq
```

### İstifadə nümunələri:
```php
$logService = app(ActivityLogService::class);

// İstifadəçi dəyişiklik logu
$logService->log(
    action: 'profile_updated',
    model: $user,
    oldData: ['email' => 'old@example.com'],
    newData: ['email' => 'new@example.com'],
    additionalData: ['notes' => 'Email manually updated by admin']
);

// Xəta logu
$logService->logError('payment_failed', 'Card declined', $order);

// 60 gündən köhnə logları sil
$deletedCount = $logService->cleanup(60);
```

## 🔗 API Endpoints

### Admin Panel
```http
GET    /api/admin/activity-logs           # Log siyahısı
GET    /api/admin/activity-logs/{id}      # Log detalları  
GET    /api/admin/activity-logs/statistics # Statistika
POST   /api/admin/activity-logs/cleanup   # Köhnə logları təmizləmə
```

### API İstifadə Nümunələri

#### Log siyahısını əldə etmək
```bash
curl -X GET "/api/admin/activity-logs?page=1&per_page=50" \
-H "Authorization: Bearer TOKEN" \
-H "Accept: application/json"
```

#### Filtrlənmiş axtarış
```bash
curl -X GET "/api/admin/activity-logs?action=login&status=success&method=POST" \
-H "Authorization: Bearer TOKEN"
```

#### Statistika əldə etmək
```bash
curl -X GET "/api/admin/activity-logs/statistics?start_date=2025-01-01&end_date=2025-01-31" \
-H "Authorization: Bearer TOKEN"
```

#### Köhnə logları təmizləmək
```bash
curl -X POST "/api/admin/activity-logs/cleanup" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{"days": 90}'
```

## 🛡️ Middleware Sistemi

### ApiLoggingMiddleware
Bu middleware API request-lərini avtomatik loglar:

**Qeyd edilən məlumatlar:**
- HTTP method, URL, headers
- Request body (həssas məlumatlar filtrlənir)
- Response status və body
- Request müddəti (millisaniyə)
- Browser, OS, device məlumatları
- IP ünvan və user agent

**Həssas məlumatların filtrlənməsi:**
```php
protected array $sensitiveData = [
    'password', 'password_confirmation', 'current_password',
    'token', 'access_token', 'refresh_token', 'api_key'
];

protected array $sensitiveHeaders = [
    'authorization', 'cookie', 'x-xsrf-token'
];
```

**Middleware tətbiqi:**
```php
// Kernel.php-də
protected $middlewareGroups = [
    'api' => [
        \App\Http\Middleware\ApiLoggingMiddleware::class,
        // digər middleware-lər
    ],
];
```

**Loglanmayan route-lar:**
```php
protected array $excludedPaths = [
    'api/health-check',
    'api/ping', 
    'api/metrics'
];
```

## 📋 Enum Class-ları

### ActivityLogActionEnum
```php
// CRUD əməliyyatları
CREATED, UPDATED, DELETED, RESTORED

// Authentication  
LOGIN, LOGOUT, LOGIN_FAILED, REGISTER, SOCIAL_LOGIN

// API əməliyyatları
API_READ, API_CREATE, API_UPDATE, API_DELETE, API_ERROR

// HTTP status
NOT_FOUND, VALIDATION_ERROR, UNAUTHORIZED, FORBIDDEN

// Image əməliyyatları
IMAGE_UPLOAD_SUCCESS, IMAGE_DELETE_SUCCESS

// System events
SERVER_ERROR, DATABASE_ERROR, FOREIGN_KEY_ERROR
```

### ActivityLogStatusEnum
```php
SUCCESS       # Uğurlu əməliyyat
ERROR         # Xətalı əməliyyat  
PENDING       # Gözləyən əməliyyat
CANCELLED     # Ləğv edilmiş əməliyyat
```

## 🎯 Model Metodları və Scope-lar

### Scope-lar
```php
// Status əsasında filterleme
ActivityLog::successful()->get();        # Uğurlu əməliyyatlar
ActivityLog::errors()->get();           # Xətalı əməliyyatlar

// Action əsasında
ActivityLog::byAction('login')->get();   # Login əməliyyatları

// İstifadəçi əsasında
ActivityLog::byUser(123)->get();         # Konkret istifadəçinin logları
```

### Helper metodları
```php
$log = ActivityLog::find(1);

// Dəyişiklikləri görmək
$changes = $log->getChanges();
// Qaytarır: ['email' => ['old' => 'old@...', 'new' => 'new@...']]

// Oxunaqlı təsvir
$description = $log->getDescription();
// Qaytarır: "John Doe updated a User"

// Accessor-lar
$log->action_description;     # "Yaradıldı", "Yeniləndi"
$log->status_description;     # "Uğurlu", "Xətalı"
```

## 🌱 Seeding Sistemi

### ActivityLogSeeder
Test məlumatları ilə çoxlu log yaradır:

```bash
php artisan db:seed --class=ActivityLogSeeder
```

**Yaradılan log növləri:**
- 25 User yaradılması/yenilənməsi/silinməsi
- 15 Setting dəyişikliyi
- Login/logout əməliyyatları
- API çağırışları
- Xəta logları
- Son 6 ayın tarix aralığında

**Statistik nümunə:**
```
| Ümumi | Yaradılma | Yenilənmə | Silinmə | Xətalar |
|-------|-----------|-----------|---------|---------|
| 2500+ | 150       | 200       | 25      | 100     |
```

## 🔍 Filter Sistemi

### ActivityLogFilter
Admin paneldə detallı axtarış:

```php
// Mövcud filterlər
'action' => 'login|created|updated'      # Əməliyyat növü
'status' => 'success|error'              # Status
'ip_address' => '192.168.1.1'           # IP ünvan
'url' => '/api/users'                    # URL axtarış
'method' => 'GET|POST|PUT|DELETE'        # HTTP method
'date_range' => ['start' => '...', 'end' => '...'] # Tarix aralığı
'search' => 'general_search_term'        # Ümumi axtarış
```

### Filter istifadəsi:
```bash
# Login əməliyyatları
GET /api/admin/activity-logs?action=login&status=success

# Konkret IP-dən gələn request-lər
GET /api/admin/activity-logs?ip_address=192.168.1.100

# Tarix aralığında xətalar
GET /api/admin/activity-logs?status=error&date_range[start]=2025-01-01&date_range[end]=2025-01-31
```

## 🚀 Performance Optimizasiyası

### Database İndekslər
```sql
-- Tez-tez axtarılan sahələr
CREATE INDEX idx_activity_model ON activity_logs(model_type, model_id);
CREATE INDEX idx_activity_action ON activity_logs(action);
CREATE INDEX idx_activity_status ON activity_logs(status);
CREATE INDEX idx_activity_user ON activity_logs(created_by);
CREATE INDEX idx_activity_date ON activity_logs(created_at);

-- Composite indekslər
CREATE INDEX idx_activity_action_status ON activity_logs(action, status);
CREATE INDEX idx_activity_user_action ON activity_logs(created_by, action);
```

### Cache strategiyası:
```php
// Statistik məlumatlar üçün cache
Cache::remember("activity_stats_{$startDate}_{$endDate}", 3600, function () {
    return $this->calculateStatistics($startDate, $endDate);
});

// Tez-tez istifadə olunan filterlər
Cache::remember("activity_filters", 1800, function () {
    return $this->getAvailableFilters();
});
```

### Avtomatik təmizlik:
```php
// Console/Kernel.php-də scheduled command
$schedule->command('activity-logs:cleanup --days=90')
    ->daily()
    ->description('Clean up old activity logs');
```

## 🧪 Testing

### Unit Test Nümunəsi
```php
public function test_can_log_user_creation()
{
    $user = User::factory()->create();
    
    $this->activityLogService->log(
        action: 'created',
        model: $user,
        newData: $user->toArray()
    );
    
    $this->assertDatabaseHas('activity_logs', [
        'action' => 'created',
        'model_type' => User::class,
        'model_id' => $user->id,
        'status' => 'success'
    ]);
}

public function test_middleware_logs_api_requests()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->getJson('/api/admin/users');
    
    $response->assertOk();
    
    $this->assertDatabaseHas('activity_logs', [
        'action' => 'api_read',
        'method' => 'GET',
        'url' => 'http://localhost/api/admin/users'
    ]);
}
```

### API Test
```php
public function test_admin_can_view_activity_logs()
{
    $admin = User::factory()->admin()->create();
    
    // Bir neçə log yaratmaq
    ActivityLog::factory()->count(5)->create();
    
    $response = $this->actingAs($admin)
        ->getJson('/api/admin/activity-logs');
        
    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'action', 'status', 'created_at']
            ]
        ]);
}
```

## ⚠️ Təhlükəsizlik və Performance

### Təhlükəsizlik:
1. **Həssas məlumat filtri** - Şifrə və token-lar qeyd edilmir
2. **IP tracking** - Təhlükəsizlik təhlili üçün
3. **User agent logging** - Bot və automation aşkarlama
4. **Error tracking** - System vulnerability monitoring

### Performance tövsiyələri:
1. **Regular cleanup** - Köhnə logları mütəmadi silmək
2. **Indexing** - Database performance üçün indekslər
3. **Exclude paths** - Lazımsız endpoint-ləri exclude etmək
4. **Cache statistics** - Analitik məlumatları cache etmək

### Storage optimization:
```php
// Partitioning strategy (böyük sistemlər üçün)
// Aylıq partition-lar yaratmaq
CREATE TABLE activity_logs_2025_01 PARTITION OF activity_logs 
FOR VALUES FROM ('2025-01-01') TO ('2025-02-01');
```

## 📋 İcazə Sistemi

**Tələb olunan icazələr:**
- `activity_log_read` - Log siyahısını görüntüləmə
- `activity_log_delete` - Köhnə logları təmizləmə
- `activity_log_export` - Logları export etmə

## 🔄 Yenilənmə Qeydləri

**v1.0.0** (2025-01-15)
- İlkin versiya
- Hərtərəfli activity tracking
- API middleware logunun
- Təhlükəsizlik və audit dəstəyi
- Statistics və reporting
