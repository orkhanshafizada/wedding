# Company Management Service - README

## 📋 Ümumi Məlumat

Laravel 12 əsaslı hərtərəfli şirkət idarəetmə sistemi. Multi-layered architecture, voting sistemi, paket idarəetməsi və banner sistemi ilə təchiz olunmuş enterprise səviyyəli business management platforması.

### Əsas məqsəd:
- Şirkət yaradılması və idarəetməsi
- Paket əsaslı elan limitləri
- Voting və reyting sistemi
- Banner və media management
- Kateqoriya təyinatı
- Status əsaslı workflow

## 🎯 Əsas Xüsusiyyətlər

- ✅ **Company Registration** - User və admin tərəfindən şirkət yaratma
- ✅ **Package System** - Elan limitləri və balance management
- ✅ **Voting System** - Multi-criteria rating system
- ✅ **Banner Management** - Position-based banner system
- ✅ **Gallery System** - Image management və thumbnails
- ✅ **Category Assignment** - Multi-category support
- ✅ **Status Workflow** - Request → Pending → Active workflow
- ✅ **Advanced Statistics** - Comprehensive analytics
- ✅ **Location Support** - City integration və address mapping
- ✅ **Contact Management** - Phone, email, social media
- ✅ **Working Hours** - Business hours management

## 🏗️ Sistem Strukturu

### Əsas Komponentlər
```
app/
├── Services/Module/
│   ├── CompanyService.php              # Əsas business logic
│   ├── CompanyVoteService.php          # Voting management
│   ├── CompanyPackageService.php       # Package management
│   ├── CompanyTypeService.php          # Type management
│   └── CompanyBenefitService.php       # Benefit management
├── Repositories/Module/
│   └── [Company*]Repository.php        # Data access layer
├── Models/
│   ├── Company.php                     # Əsas şirkət modeli
│   ├── CompanyVote.php                 # Voting sistemi
│   ├── CompanyBanner.php               # Banner management
│   ├── CompanyPackage.php              # Paket sistemi
│   └── [Other]Company*.php             # Digər əlaqəli modellər
├── Http/Controllers/Api/
│   ├── Admin/Company*Controller.php    # Admin API
│   └── Front/CompanyController.php     # Public API
├── Enums/
│   ├── CompanyStatusEnum.php           # Status definition
│   └── CompanyBannerPositionEnum.php   # Banner positions
└── Services/Filter/
    └── Company*Filter.php              # Advanced filtering
```

## 📊 Database Strukturu

### Companies Table
```php
// Ana şirkət cədvəli
Schema::create('companies', function (Blueprint $table) {
    $table->id();
    $table->key();                       # UUID
    $table->slug();                      # SEO URL
    $table->foreignId('user_id');        # Şirkət sahibi
    $table->foreignId('company_package_id')->nullable();
    $table->foreignId('company_type_id')->nullable();
    $table->foreignId('city_id')->nullable();
    
    // Əsas məlumatlar
    $table->string('name');              # Şirkət adı
    $table->string('email')->unique();   # Email
    $table->json('phones');              # Telefon nömrələri
    $table->string('vat_id')->nullable(); # VÖEN
    $table->decimal('balance');          # Balans
    
    // Media files
    $table->photo('logo');               # Logo
    $table->photo('wallpaper');          # Wallpaper
    $table->photos('galleries');         # Galeriya
    $table->photo('passport');           # Passport skan
    
    // Business info
    $table->dateTime('deadline');        # Paket müddəti
    $table->integer('listing_limit');    # Elan limiti
    $table->string('slogan')->nullable();
    $table->text('description')->nullable();
    $table->json('keywords')->nullable();
    $table->json('working_hours')->nullable();
    $table->json('social_media')->nullable();
    $table->json('video_links')->nullable();
    $table->json('address')->nullable();
    
    // Status və statistika
    $table->boolean('is_verified');
    $table->string('status');            # CompanyStatusEnum
    $table->integer('views');
});
```

### Əlaqəli Cədvəllər
```php
// Şirkət paketləri
company_packages: balance, amount, limit, translates

// Şirkət tipleri  
company_types: translates, order

// Şirkət bannerleri
company_banners: position, color, link, photo_path

// Şirkət kateqoriyaları
company_categories: main_category_id, category_id

// Voting sistemi
company_votes: rating, comment, voting_system_id

// Paket xidmətləri
company_package_payment_services: free_limit
```

## 🔧 Service Metodları

### CompanyService
```php
// CRUD əməliyyatları
create(array $data)                    # Şirkət yaratmaq
update(int $id, array $data)           # Şirkət yeniləmək
delete(int $id): bool                  # Şirkət silmək
findById(int $id): Model               # ID ilə tapmaq

// Xüsusi metodlar
createCompanyForUser(User $user, array $data) # İstifadəçi üçün şirkət
updateStatus(int $id, string $status)  # Status dəyişmək
getStatistics(int $id): array          # Şirkət statistikası
getDashboardData(): array              # Admin dashboard

// Banner idarəetmə
getBanners(int $id)                    # Banner siyahısı
addBanner(int $id, array $data)        # Banner əlavə etmək
deleteBanner(int $id, int $bannerId)   # Banner silmək

// Konfiqurasiya
config(int $id)                        # Config məlumatları
configSave(int $id, array $data)       # Config saxlamaq

// Public metodlar
fetchCompanies()                       # Şirkət siyahısı
fetchCompany($slug)                    # Şirkət detalları
fetchCompanyListings($company)         # Şirkət elanları
fetchCompanyPackages()                 # Paket siyahısı
fetchCompanyBenefits()                 # Fayda siyahısı
```

### CompanyVoteService
```php
vote(array $data, Company $company)    # Şirkətə səs vermək
getVotingSystems(Company $company)     # Voting sistemləri
getCompanyVotes(Company $company)      # Şirkət səsləri
getUserVote(Company $company, $systemId) # İstifadəçi səsi
```

## 🚀 API Endpoints

### Admin API
```http
# CRUD əməliyyatları
GET    /api/crud/companies             # Şirkət siyahısı
POST   /api/crud/companies             # Yeni şirkət
GET    /api/crud/companies/{id}        # Şirkət detalları
PUT    /api/crud/companies/{id}        # Şirkət yenilənməsi
DELETE /api/crud/companies/{id}        # Şirkət silmək

# Status idarəetmə
PUT    /api/crud/companies/{id}/action # Status dəyişmək

# Banner management
GET    /api/crud/companies/{id}/banners       # Banner siyahısı
POST   /api/crud/companies/{id}/banners       # Banner əlavə etmək
DELETE /api/crud/companies/{id}/banners/{bid} # Banner silmək

# Konfigurasiya
GET    /api/crud/companies/{id}/config        # Config əldə etmək
POST   /api/crud/companies/{id}/config        # Config saxlamaq

# Dashboard
GET    /api/crud/companies/dashboard          # Admin dashboard
```

### Public API
```http
# Şirkət siyahısı və detallar
GET    /api/companies                  # Şirkət siyahısı
GET    /api/companies/{slug}           # Şirkət detalları
POST   /api/companies                  # Şirkət yaratmaq

# Şirkət əlaqəli məlumatlar
GET    /api/companies/{company}/comments      # Şəxhələr
GET    /api/companies/{company}/listings      # Elanlar

# Sistem məlumatları
GET    /api/companies/packages         # Paket siyahısı
GET    /api/companies/benefits         # Fayda siyahısı
```

## 💻 API İstifadə Nümunələri

### Şirkət yaratmaq (Authentication ilə)
```bash
curl -X POST "/api/companies" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "name": "Tech Solutions MMC",
    "email": "info@techsolutions.az",
    "phone": "+994501234567",
    "address": "Bakı şəhəri, Yasamal rayonu",
    "vat_id": "1234567890",
    "passport_path": "data:image/jpeg;base64,...",
    "company_type_id": 1,
    "company_package_id": 2,
    "city_id": 1
}'
```

### Şirkət yaratmaq (Qeydiyyatsız)
```bash
curl -X POST "/api/companies" \
-H "Content-Type: application/json" \
-d '{
    "name": "Yeni Şirkət",
    "email": "newcompany@example.com",
    "personal_name": "Ali",
    "personal_surname": "Məmmədov",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!",
    "phone": "+994501234567",
    "address": "Bakı şəhəri",
    "passport_path": "data:image/jpeg;base64,...",
    "company_type_id": 1,
    "company_package_id": 1,
    "city_id": 1
}'
```

Response:
```json
{
    "company": {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Tech Solutions MMC",
        "email": "info@techsolutions.az", 
        "status": "pending",
        "status_text": "Gözləmədə",
        "balance": 0,
        "listing_limit": 0,
        "package": {
            "id": 2,
            "name": "Premium Paket",
            "balance": 250,
            "limit": 50
        }
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "message": "Şirkət uğurla yaradıldı"
}
```

### Admin tərəfindən status dəyişmək
```bash
curl -X PUT "/api/crud/companies/1/action" \
-H "Authorization: Bearer ADMIN_TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "status": "active"
}'
```

### Banner əlavə etmək
```bash
curl -X POST "/api/crud/companies/1/banners" \
-H "Authorization: Bearer ADMIN_TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "position": "page_desktop_top",
    "color": "#FF5733",
    "link": "https://example.com",
    "photo_path": "data:image/jpeg;base64,..."
}'
```

## 📈 Status Workflow

### Şirkət Statusları
```php
enum CompanyStatusEnum {
    Request = 'request';        # İstək göndərib
    Pending = 'pending';        # Gözləmədə  
    Active = 'active';          # Aktiv
    Suspended = 'suspended';    # Dayandırılıb
    Expired = 'expired';        # Müddəti bitib
    Blocked = 'blocked';        # Bloklanıb
    Rejected = 'rejected';      # Rədd edilib
}
```

### Status Dəyişikliyi Prosesi
1. **Request/Pending** → İlkin yaradılma
2. **Active** → Admin təsdiqi + paket aktivləşdirilməsi
3. **Suspended** → Müvəqqəti dayandırma
4. **Expired** → Paket müddətinin bitməsi
5. **Blocked** → Qayda pozuntuları
6. **Rejected** → Rədd edilmə

### Aktivləşdirmə Prosesi
```php
// Status "active" olduqda avtomatik proseslər
if ($status === CompanyStatusEnum::Active) {
    $company->update([
        'balance' => $company->balance + $package->balance,
        'listing_limit' => $package->limit,
        'deadline' => now()->addMonth()
    ]);
}
```

## 🎯 Voting Sistemi

### Voting System Features
```php
// Şirkət üçün səs vermək
CompanyVoteService::vote([
    'voting_system_id' => 1,
    'rating' => 4.5,
    'comment' => 'Çox yaxşı xidmət'
], $company);

// Şirkət reytinqləri
$company->getCompanyVoteStats();
// Returns: avg_rating, votes_count, voting_system_ratings
```

### Vote Model
```php
class CompanyVote {
    protected $fillable = [
        'company_id',
        'user_id', 
        'voting_system_id',
        'rating',           # 1-5 arası
        'comment',          # İstəyə bağlı
        'is_verified'       # Təsdiqlənmiş alış
    ];
    
    // Unique constraint: bir user, bir company, bir voting system
}
```

## 📦 Package Sistemi

### Package Structure
```php
class CompanyPackage {
    protected $fillable = [
        'translates',       # Çoxdilli ad
        'amount',          # Qiymət
        'balance',         # Hədiyyə balans
        'limit',           # Elan limiti
    ];
}
```

### Package Benefits
```php
// Paket aktivləşdikdə:
- balance += package.balance
- listing_limit = package.limit  
- deadline = now() + 1 month
- free service limits assigned
```

## 🎨 Banner Sistemi

### Banner Positions
```php
enum CompanyBannerPositionEnum {
    PageDesktopTop = 'page_desktop_top';    # Üst (Web - 160px)
    PageMobileTop = 'page_mobile_top';      # Üst (Mobil - 110px) 
    PageLeftRight = 'page_left_right';      # Yan (300x900px)
}
```

### Banner Management
```php
// Banner əlavə etmək
$company->banners()->create([
    'position' => 'page_desktop_top',
    'color' => '#FF5733',
    'link' => 'https://promo.com',
    'photo_path' => 'banners/photo.jpg'
]);
```

## 🔍 Filter Sistemi

### CompanyFilter Options
```php
protected array $filters = [
    'search',              # Ad, email, VÖEN axtarışı
    'category',            # Kateqoriya filtri
    'status',              # Status filtri  
    'type',                # Şirkət tipi
    'package',             # Paket filtri
    'date_range',          # Tarix aralığı
    'balance_range',       # Balans aralığı
    'listing_limit_range', # Limit aralığı
    'is_active'            # Aktivlik
];
```

### Filter Usage
```bash
# Status və kateqoriya filtri
GET /api/companies?status=active&category=technology

# Balans aralığı
GET /api/companies?balance_range[min]=100&balance_range[max]=1000

# Tarix və axtarış
GET /api/companies?search=tech&date_range[from]=2024-01-01
```

## 📊 Dashboard & Analytics

### Company Statistics
```php
CompanyService::getDashboardData() returns:
- stats: total, active, inactive companies
- status_stats: breakdown by status
- package_stats: usage by package
- category_stats: distribution by category  
- banner_stats: banner usage
- type_stats: distribution by type
```

### Individual Company Stats
```php
CompanyService::getStatistics($id) returns:
- total_views: görüntülənmə sayı
- listing_count: ümumi elan sayı
- active_listing_count: aktiv elan sayı
- remaining_balance: qalan balans
- remaining_listing_limit: qalan elan limiti
- days_until_deadline: müddət qalması
```

## 🌱 Seeding Sistemi

### CompanySeeder
50 test şirkəti və əlaqəli məlumatları yaradır:

```bash
php artisan db:seed --class=CompanySeeder
```

**Yaradılan məlumatlar:**
- **50 şirkət**: Random statuslarla
- **100+ banner**: Müxtəlif mövqelərdə
- **Kateqoriya təyinatları**: 1-3 kateqoriya hər şirkət
- **Working hours**: Realistic iş saatları
- **Contact info**: Telefon, email, social media
- **Media files**: Logo, wallpaper, passport, gallery

### CompanyOptionsSeeder
Sistem parametrlərini yaradır:

```bash
php artisan db:seed --class=CompanyOptionsSeeder
```

**Yaradılan məlumatlar:**
- **Company Types**: Avtosalon, Mağaza, Klinika, Şirkət
- **Company Packages**: 4 fərqli paket (100-1200 elan limiti)
- **Company Benefits**: 11 fayda (multi-language)
- **Package Services**: Payment service əlaqələndirmələri

## 🔔 Notification System

### Auto Notifications
```php
// Status dəyişikliklərində
- CompanyStatusEnum::Active → Aktivləşdirmə bildirişi
- CompanyStatusEnum::Expired → Müddət bitmə bildirişi
- CompanyStatusEnum::Suspended → Dayandırma bildirişi

// Voting-də
- NotificationTypeEnum::NEW_COMPANY_VOTE → Yeni səs bildirişi
```

## 🔧 Validation Rules

### Company Creation
```php
'name' => 'required|string|max:255'
'email' => 'required|email|unique:companies,email'
'phones' => 'required|array'
'phones.*.number' => 'required|string'
'vat_id' => 'nullable|string'              # VÖEN
'logo_path' => 'required|string|base64_image'
'passport_path' => 'required|string|base64_image'
'company_type_id' => 'required|exists:company_types,id'
'company_package_id' => 'required|exists:company_packages,id'
'working_hours' => 'nullable|array'
'social_media' => 'nullable|array'
'address' => 'nullable|array'
```

### Banner Rules
```php
'position' => 'required|in:page_desktop_top,page_mobile_top,page_left_right'
'color' => 'nullable|string'
'link' => 'nullable|url'
'photo_path' => 'required|string|base64_image'
```

## 🔐 Permission Sistemi

**Tələb olunan icazələr:**
- `company_read` - Şirkət siyahısını görüntüləmə
- `company_create` - Yeni şirkət yaratma
- `company_update` - Şirkət yenilənməsi
- `company_delete` - Şirkət silmə
- `company_status` - Status dəyişdirmə

## 🚀 Performance Optimizasiyası

### Database İndekslər
```php
// Migration-larda avtomatik yaradılır
$table->index(['status', 'is_active']);
$table->index(['company_type_id']);
$table->index(['company_package_id']);
$table->index(['city_id']);
$table->index(['created_at']);
$table->unique(['email']);
```

### Repository Cache
```php
// CompanyRepository cache strategiyası
$companyRepository->setUseCache(true);

// Cache keys:
- "companies_findById_{id}"
- "companies_paginateAndFilter_{params_hash}"
- "companies_findActiveList"
```

## 🧪 Testing Nümunələri

### Service Test
```php
public function test_can_create_company_for_user()
{
    $user = User::factory()->create();
    $data = [
        'name' => 'Test Company',
        'email' => 'test@company.az',
        'company_type_id' => 1,
        'company_package_id' => 1
    ];
    
    $company = $this->companyService->createCompanyForUser($user, $data);
    
    $this->assertEquals('pending', $company->status);
    $this->assertEquals($user->id, $company->user_id);
}

public function test_package_activation_adds_balance()
{
    $company = Company::factory()->create(['status' => 'pending']);
    
    $this->companyService->updateStatus($company->id, 'active');
    
    $company->refresh();
    $this->assertTrue($company->balance > 0);
    $this->assertTrue($company->listing_limit > 0);
}
```

### API Test
```php
public function test_user_can_create_company()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->postJson('/api/companies', [
        'name' => 'New Company',
        'email' => 'new@company.az',
        // ... digər sahələr
    ]);
    
    $response->assertStatus(201)
        ->assertJsonStructure(['company', 'message']);
}
```

---

**💡 Qeyd:** Sistem həm authenticated həm də guest user-lər üçün şirkət yaradılmasını dəstəkləyir. Guest user-lər üçün avtomatik qeydiyyat prosesi işləyir.
