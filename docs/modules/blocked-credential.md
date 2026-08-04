# Blocked Credentials Service - README

## 📋 Ümumi Məlumat

Laravel 12 əsaslı təhlükəsizlik sistemi. Email, telefon və IP ünvanlarını bloklamaq üçün hazırlanmışdır. Spam, suiistifadə və təhlükəsizlik pozuntularını qarşısını almaq məqsədi daşıyır.

### Əsas məqsəd:
- Problemli email/telefon/IP ünvanlarını bloklamaq
- Müvəqqəti və daimi bloklar yaratmaq
- Avtomatik middleware ilə yoxlama
- Admin paneldən blok idarəetməsi

## 🎯 Əsas Xüsusiyyətlər

- ✅ **3 növ blok** - Email, Telefon, IP ünvanı
- ✅ **Müddətli/Daimi bloklar** - Müvəqqəti və ya daimi qadağa
- ✅ **Avtomatik yoxlama** - Middleware vasitəsilə real-time nəzarət
- ✅ **Blok səbəbi** - Hər blok üçün izahat
- ✅ **Müddət idarəetməsi** - Blok müddətini uzatma/dəyişmə
- ✅ **Auto seeding** - Test məlumatları ilə nümunə bloklar

## 🏗️ Fayl Strukturu

```
app/
├── Services/Module/BlockedCredentialService.php     # Biznes məntiq
├── Repositories/Module/BlockedCredentialRepository.php # Database əməliyyatları
├── Models/BlockedCredential.php                     # Model və əlaqələr
├── Http/Controllers/Api/Admin/BlockedCredentialController.php # Admin API
├── Http/Middleware/CheckBlockedCredentials.php      # Avtomatik yoxlama
├── Enums/CredentialTypeEnum.php                     # Blok növləri
└── Services/Filter/BlockedCredentialFilter.php      # Filter sistemi

database/
├── migrations/create_blocked_credentials_table.php  # Database strukturu
└── seeders/BlockedCredentialSeeder.php             # Test məlumatları
```

## 📊 Database Strukturu

### Blocked Credentials Table
```sql
CREATE TABLE blocked_credentials (
    id BIGINT PRIMARY KEY,
    uuid VARCHAR(36),
    type VARCHAR(255),              # email, phone, ip
    value VARCHAR(255),             # Actual credential dəyəri
    reason TEXT,                    # Blok səbəbi
    blocked_until TIMESTAMP NULL,   # NULL = daimi blok
    is_active BOOLEAN DEFAULT TRUE, # Aktiv/deaktiv status
    created_by BIGINT,              # Kim blokadı
    updated_by BIGINT,              # Kim yenilədi
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,           # Soft delete
    
    UNIQUE(type, value)             # Eyni credential təkrarlanmasın
);
```

### Blok Növləri (CredentialTypeEnum)
```php
const Email = 'email';      # Email ünvanları
const Phone = 'phone';      # Telefon nömrələri  
const IP = 'ip';           # IP ünvanları
```

## 🔧 Service Metodları

### BlockedCredentialService
```php
// CRUD əməliyyatları (BaseCrudService-dən)
create(array $data)                          # Yeni blok yaratmaq
update(int $id, array $data)                 # Bloku yeniləmək
delete(int $id)                              # Bloku silmək
findById(int $id)                            # ID ilə tapmaq

// Xüsusi metodlar
getBlockInfo(string $type, string $value): ?object   # Aktiv blok yoxlamaq
```

### İstifadə nümunələri:
```php
$service = app(BlockedCredentialService::class);

// Email bloku yoxlamaq
$emailBlock = $service->getBlockInfo('email', 'spam@example.com');
if ($emailBlock && $emailBlock->isActive()) {
    // Email bloklanıb
}

// Yeni blok yaratmaq
$service->create([
    'type' => 'email',
    'value' => 'bad@example.com',
    'reason' => 'Spam göndərmə',
    'blocked_until' => Carbon::now()->addDays(30) // 30 gün
]);
```

## 🔗 API Endpoints

### Admin Panel
```http
GET    /api/admin/blocked-credentials       # Blok siyahısı
POST   /api/admin/blocked-credentials       # Yeni blok yaratma
GET    /api/admin/blocked-credentials/{id}  # Blok detalları
PUT    /api/admin/blocked-credentials/{id}  # Blok yeniləmə
DELETE /api/admin/blocked-credentials/{id}  # Blok silmə
```

### API İstifadə Nümunələri

#### Blok siyahısını əldə etmək
```bash
curl -X GET "/api/admin/blocked-credentials" \
-H "Authorization: Bearer TOKEN" \
-H "Accept: application/json"
```

#### Yeni blok yaratmaq
```bash
curl -X POST "/api/admin/blocked-credentials" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "type": "email",
    "value": "spam@example.com",
    "reason": "Çoxlu spam göndərmə",
    "blocked_until": "2025-02-15T00:00:00Z"
}'
```

#### Daimi blok yaratmaq
```bash
curl -X POST "/api/admin/blocked-credentials" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "type": "ip",
    "value": "192.168.1.100",
    "reason": "Şübhəli aktivlik",
    "blocked_until": null
}'
```

## 🛡️ Middleware Sistemi

### CheckBlockedCredentials Middleware
Bu middleware hər request-də avtomatik yoxlama aparır:

**Yoxlama qaydası:**
1. **IP yoxlaması** - Hər request üçün
2. **Email/Telefon yoxlaması** - Yalnız auth route-lar üçün

**Auth route-ları:**
- `/api/auth/*` - Bütün auth endpoint-ləri
- `login`, `register` - Adlandırılmış route-lar
- `forgot-password`, `reset-password` - Şifrə bərpası

### Middleware tətbiqi:
```php
// Kernel.php-də
protected $middlewareGroups = [
    'api' => [
        \App\Http\Middleware\CheckBlockedCredentials::class,
        // digər middleware-lər
    ],
];

// Və ya konkret route-lar üçün
Route::middleware('blocked.credentials')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});
```

### Blok response formatı:
```json
{
    "code": "23000",
    "message": "Email ünvanınız bloklanıb",
    "details": {
        "reason": "Spam göndərmə",
        "remaining_time": "15 gün sonra"
    }
}
```

## 📋 Model Metodları və Scope-lar

### Scope-lar
```php
// Credential növünə görə filterleme
BlockedCredential::emails()->get();          # Email blokları
BlockedCredential::phones()->get();          # Telefon blokları  
BlockedCredential::ips()->get();            # IP blokları

// Müddətə görə filterleme
BlockedCredential::notExpired()->get();      # Aktiv bloklar
BlockedCredential::expired()->get();         # Müddəti bitmiş
BlockedCredential::permanent()->get();       # Daimi bloklar
```

### Helper metodları
```php
$block = BlockedCredential::find(1);

// Status yoxlaması
$block->isPermanent();      # Daimi blokmu?
$block->isExpired();        # Müddəti bitibmi?
$block->isActive();         # Aktivdir? (is_active + müddət)

// Əməliyyatlar
$block->deactivate();                    # Bloku deaktiv et
$block->extend(Carbon::now()->addDays(30)); # Müddəti uzat
$block->makePermanent();                 # Daimi et
```

### Attribute-lar
```php
// Avtomatik hesablanan sahələr
$block->type_text;          # "Email ünvanı", "Telefon nömrəsi"
$block->remaining_time;     # "2 gün sonra", "Daimi", "Müddəti bitib"
```

## 🌱 Seeding Sistemi

### BlockedCredentialSeeder
Test məlumatları ilə nümunə bloklar yaradır:

```bash
php artisan db:seed --class=BlockedCredentialSeeder
```

**Yaradılan nümunələr:**
- 5 spam email ünvanı
- 5 şübhəli telefon nömrəsi
- 5 problemli IP ünvanı
- Müxtəlif blok səbəbləri
- Müvəqqəti və daimi bloklar

## 🔍 Filter Sistemi

### BlockedCredentialFilter
Admin paneldə axtarış və filter:

```php
// Mövcud filterlər
'type' => 'email|phone|ip'           # Blok növü
'value' => 'search_term'             # Credential axtarışı
'reason' => 'search_term'            # Səbəb axtarışı
```

### Filter istifadəsi:
```bash
# Email bloklarını əldə etmək
GET /api/admin/blocked-credentials?type=email

# Konkret email axtarmaq
GET /api/admin/blocked-credentials?value=spam@example.com

# Səbəbə görə axtarmaq
GET /api/admin/blocked-credentials?reason=spam
```

## 🔧 Validation Rules

### BlockCredentialRequest
```php
'type' => 'required|in:email,phone,ip'      # Düzgün blok növü
'value' => 'required|string|max:255'         # Credential dəyəri
'reason' => 'required|string|max:1000'       # Blok səbəbi
'blocked_until' => 'nullable|date|after:now' # Gələcək tarix
'is_active' => 'boolean'                     # Status
```

### Xüsusi validation qaydaları:
- Email bloku üçün: `email` formatı yoxlanır
- Telefon bloku üçün: telefon format pattern
- IP bloku üçün: `ip` validation rule
- Eyni credential təkrarlanmır (unique constraint)

## 🚀 Performance Optimizasiyası

### Database İndekslər
```sql
-- Sürətli axtarış üçün
CREATE INDEX idx_blocked_type_value ON blocked_credentials(type, value);
CREATE INDEX idx_blocked_active ON blocked_credentials(is_active);
CREATE INDEX idx_blocked_until ON blocked_credentials(blocked_until);

-- Composite indeks
CREATE INDEX idx_blocked_active_type ON blocked_credentials(is_active, type, value);
```

### Cache strategiyası:
```php
// Tez-tez yoxlanan IP/email-lar üçün cache
Cache::remember("blocked_email_{$email}", 300, function () use ($email) {
    return BlockedCredential::emails()
        ->where('value', $email)
        ->notExpired()
        ->first();
});
```

## 🧪 Testing

### Unit Test Nümunəsi
```php
public function test_can_create_email_block()
{
    $data = [
        'type' => 'email',
        'value' => 'test@spam.com',
        'reason' => 'Test bloku',
        'blocked_until' => Carbon::now()->addDays(7)
    ];
    
    $block = $this->service->create($data);
    
    $this->assertEquals('email', $block->type);
    $this->assertEquals('test@spam.com', $block->value);
    $this->assertFalse($block->isPermanent());
}

public function test_middleware_blocks_blocked_email()
{
    // Email bloku yaratmaq
    BlockedCredential::create([
        'type' => 'email',
        'value' => 'blocked@example.com',
        'reason' => 'Test',
        'is_active' => true
    ]);
    
    // Login cəhdi
    $response = $this->postJson('/api/auth/login', [
        'email' => 'blocked@example.com',
        'password' => 'password'
    ]);
    
    $response->assertStatus(403)
        ->assertJson(['code' => '23000']);
}
```

### API Test
```php
public function test_admin_can_create_block()
{
    $admin = User::factory()->admin()->create();
    
    $data = [
        'type' => 'ip',
        'value' => '192.168.1.1',
        'reason' => 'Şübhəli aktivlik',
        'blocked_until' => null // Daimi
    ];
    
    $response = $this->actingAs($admin)
        ->postJson('/api/admin/blocked-credentials', $data);
        
    $response->assertStatus(201);
    $this->assertDatabaseHas('blocked_credentials', [
        'type' => 'ip',
        'value' => '192.168.1.1'
    ]);
}
```

## ⚠️ Təhlükəsizlik və Best Practice

### Təhlükəsizlik tədbirləri:
1. **Unique constraint** - Eyni credential təkrarlanmır
2. **Soft delete** - Blok tarixi saxlanır
3. **Audit trail** - Kim, nə vaxt əlavə/dəyişib
4. **Rate limiting** - Admin panel-də blok yaratma məhdudiyyəti

### İstifadə tövsiyələri:
1. **Müvəqqəti bloklar** - İlk pozuntu üçün
2. **Daimi bloklar** - Ciddi təhlükəsizlik pozuntuları
3. **Düzenli təmizlik** - Köhnə expired blokları silmək
4. **Monitoring** - Blok statistikalarının izlənməsi

## 📋 İcazə Sistemi

**Tələb olunan icazələr:**
- `blocked_credential_read` - Blok siyahısını görmə
- `blocked_credential_create` - Yeni blok yaratma
- `blocked_credential_update` - Bloku dəyişdirmə
- `blocked_credential_delete` - Bloku silmə

## 🔄 Avtomatik Təmizlik

### Scheduled Command (gələcək versiya):
```php
// Console/Kernel.php
$schedule->command('blocked-credentials:cleanup')
    ->daily()
    ->description('Köhnə blokları təmizləyir');
```

## 🔄 Yenilənmə Qeydləri

**v1.0.0** (2025-01-15)
- İlkin versiya
- Email/Phone/IP blok sistemi
- Middleware integrasiyası
- Admin panel idarəetməsi
- Müddətli/Daimi blok dəstəyi
