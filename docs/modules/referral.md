# Referral Management Service

<img src="https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12.0"/>
<img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2"/>
<img src="https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge" alt="MIT License"/>

## 📋 Ümumi Məlumat

Laravel 12 əsaslı hərtərəfli referral sistemi. İstifadəçilər bir-birlərini dəvət edərək bonus qazana bilər. Sistem referral kodları, bonus hesablama, status idarəetməsi və statistika xüsusiyyətləri təqdim edir.

### Əsas məqsəd:
- İstifadəçilər arası dəvət sistemi
- Bonus qazanc və hesablama sistemi
- Status əsaslı iş prosesi (Pending, Completed, Rejected)
- Avtomatik bildiriş sistemi
- Detallı statistika və hesabatlar

## 🎯 Əsas Xüsusiyyətlər

- ✅ **Unikal Referral Kodları** - Hər istifadəçi üçün unikal referral kodu
- ✅ **Bonus Sistemi** - Dəvət edən və olunan üçün müxtəlif bonus məbləğləri
- ✅ **Status Workflow** - Pending → Completed → Rejected
- ✅ **Statistika Dashboard** - Detallı hesabatlar və analitika
- ✅ **User Dashboard** - İstifadəçilər üçün şəxsi referral statistikası
- ✅ **Auto Validation** - Referral limitləri və etibarlılıq yoxlaması
- ✅ **Listing Rewards** - Elan sayına görə əlavə bonuslar
- ✅ **Admin Panel** - Tam moderasiya imkanları

## 📦 Quraşdırma

```bash
# Layihəni klonlamaq
git clone [repository-url]

# Asılılıqları quraşdırmaq
composer install

# .env faylını hazırlamaq
cp .env.example .env
php artisan key:generate

# Miqrasiyaları və test məlumatlarını yükləmək
php artisan migrate
php artisan db:seed --class=ReferralSeeder

# Xidməti başlatmaq
php artisan serve
```

## 🏗️ Fayl Strukturu

```
app/
├── Services/Module/ReferralService.php       # Biznes məntiq
├── Repositories/Module/ReferralRepository.php # Database əməliyyatları
├── Models/Referral.php                       # Referral model
├── Http/Controllers/Api/
│   ├── Admin/ReferralController.php          # Admin API
│   └── Front/ReferralController.php          # User API
├── Enums/
│   └── ReferralStatusEnum.php                # Referral statusları
└── Services/Filter/ReferralFilter.php        # Filter sistemi

database/
├── migrations/create_referrals_table.php     # Database strukturu
└── seeders/ReferralSeeder.php                # Test məlumatları
```

## 📊 Database Strukturu

### Referrals Table Migration
```php
Schema::create('referrals', function (Blueprint $table) {
    $table->id();
    $table->key(); // uuid macro'su

    // Əsas əlaqələr
    $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete(); // Dəvət edən istifadəçi
    $table->foreignId('referee_id')->constrained('users')->cascadeOnDelete(); // Dəvət olunan istifadəçi

    // Bonus məbləğləri
    $table->decimal('referrer_bonus', 10, 2); // Dəvət edənin qazandığı bonus
    $table->decimal('referee_bonus', 10, 2); // Dəvət olunanın qazandığı bonus

    // Status
    $table->string('status')->default(ReferralStatusEnum::Pending); // ReferralStatusEnum
    $table->text('rejection_reason')->nullable(); // Rədd edilmə səbəbi (moderator tərəfindən)

    $table->trackable(); // created_by, updated_by macro'su
    $table->timestamps();
    $table->softDeletes();
});
```

## 🔧 Service Metodları

### ReferralService
```php
// Referral yaratmaq
createReferral(User $referrer, User $referee): Referral

// Status dəyişikliyi
updateStatus(Referral $referral, string $status, ?string $reason = null): void

// Toplu silmə əməliyyatı
bulkDelete($ids)

// Elan sayına görə bonus
processListingBonus(User $user): void

// Statistika
getStatistics(): array

// Referralları əldə etmək
getReferrals(array $filters = []): LengthAwarePaginator|Collection

// Bonus hesablama
calculateBonusAmounts(User $referrer, User $referee): array
```

### İstifadə nümunələri:
```php
$referralService = app(ReferralService::class);

// İstifadəçilər arası referral yaratmaq
$referrer = User::find(1); // Dəvət edən
$referee = User::find(2);  // Dəvət olunan
$referral = $referralService->createReferral($referrer, $referee);

// Referral statusunu dəyişmək
$referralService->updateStatus(
    referral: $referral,
    status: ReferralStatusEnum::Completed
);

// Statistika əldə etmək
$stats = $referralService->getStatistics();

// Elan sayına görə bonus əlavə etmək
$referralService->processListingBonus($user);
```

## 🔗 API Endpoints

### User API (Frontend)
```http
GET    /api/user/referrals                # Mənim dəvətlərim
GET    /api/user/referrals/stats          # Referral statistikam
GET    /api/user/referrals/earnings       # Qazandığım bonuslar
GET    /api/user/referrals/rewards        # Mövcud bonus şərtləri
POST   /api/user/referrals/generate-code  # Yeni referral kodu yaratmaq
GET    /api/user/referrals/validate/{code}# Referral kodunu yoxlamaq
```

### Admin API
```http
GET    /api/admin/referrals               # Bütün referrallar
GET    /api/admin/referrals/statistics    # Sistem statistikası
GET    /api/admin/referrals/filters       # Filter seçimləri
PUT    /api/admin/referrals/bulk-status   # Toplu status dəyişikliyi
DELETE /api/admin/referrals/bulk-delete   # Toplu silmə
```

## 💻 API İstifadə Nümunələri

### Referral statistikasını əldə etmək
```bash
curl -X GET "/api/user/referrals/stats" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json"
```

Response:
```json
{
    "stats": {
        "total_referrals": 15,
        "completed_referrals": 10,
        "pending_referrals": 3,
        "rejected_referrals": 2,
        "total_earnings": 100.00
    },
    "rewards": {
        "referrer_amount": 10.00,
        "referee_amount": 5.00
    },
    "referral_code": "ABC123XYZ",
    "referral_link": "https://example.com/register?ref=ABC123XYZ"
}
```

### Toplu status dəyişikliyi (Admin)
```bash
curl -X PUT "/api/admin/referrals/bulk-status" \
-H "Authorization: Bearer ADMIN_TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "ids": [1, 2, 3],
    "status": "completed",
    "reason": "İstifadəçilər tələbləri yerinə yetirib"
}'
```

### Referral sisteminin tam statistikası
```bash
curl -X GET "/api/admin/referrals/statistics" \
-H "Authorization: Bearer ADMIN_TOKEN"
```

Response:
```json
{
    "system_overview": {
        "active_referrers": 120,
        "total_referees": 450,
        "success_rate": 75.5,
        "rejection_rate": 10.2
    },
    "financial_metrics": {
        "total_paid": "4,500.00",
        "pending_payments": "1,200.00",
        "average_bonus": "10.00"
    },
    "time_based_metrics": {
        "today": {
            "referral_count": 25,
            "bonus_amount": "250.00"
        },
        "week": {
            "referral_count": 120,
            "bonus_amount": "1,200.00"
        },
        "month": {
            "referral_count": 350,
            "bonus_amount": "3,500.00"
        }
    },
    "top_referrers": [
        {
            "user": "Ali Məmmədov",
            "email": "ali@example.com",
            "referral_count": 45
        },
        {
            "user": "Səbinə Əliyeva",
            "email": "sabina@example.com",
            "referral_count": 32
        }
    ]
}
```

## 📋 Model Metodları və Enum-lar

### Referral Status Workflow
```php
// app/Enums/ReferralStatusEnum.php
enum ReferralStatusEnum {
    Pending = 'pending';    // Yeni referral
    Completed = 'completed'; // Tamamlanmış
    Rejected = 'rejected';   // Rədd edilmiş
}
```

### Referral Model Metodları
```php
class Referral extends BaseModel
{
    // Əlaqələr
    public function referrer(): BelongsTo  # Dəvət edən istifadəçi
    public function referee(): BelongsTo  # Dəvət olunan istifadəçi
    
    // Scope-lar
    public function scopePending($query)
    public function scopeCompleted($query)
    public function scopeRejected($query)
    
    // Əsas metodlar
    public function markAsCompleted(): void  # Tamamlanmış kimi işarələmək
    public function markAsRejected(string $reason = null): void # Rədd edilmiş kimi işarələmək
    
    // Helper metodlar
    public function isPending(): bool
    public function isCompleted(): bool
    public function isRejected(): bool
}
```

### User Model-ə əlavə edilən metodlar
```php
class User extends BaseModel
{
    // Referral ilə əlaqəli metodlar
    public function referrals() # Dəvət etdiklərim
    public function referees() # Dəvət olunduqlarım
    
    // Helper metodlar
    public function generateReferralCode(): string  # Yeni referral kodu yaratmaq
    public function getReferralLink(): string  # Referral linkini əldə etmək
    public function getReferralStats(): array  # Referral statistikasını əldə etmək
    public function canCreateNewReferral(): array  # Yeni referral yarada bilərmi?
    public function addReferralBonus(float $amount, string $reason = null): void  # Bonus əlavə etmək
    public function getTotalReferralEarnings(): float  # Ümumi qazancı əldə etmək
    
    // Limit yoxlamaları
    public function canInviteMore(): bool  # Gündəlik limit aşılmayıb?
    public function isReferralLinkValid(): bool  # Referral link etibarlıdır?
}
```

### Praktik İstifadə
```php
$user = User::find(1);

// Referral kodu və linki əldə etmək
$referralCode = $user->referral_code;
$referralLink = $user->getReferralLink();

// Referral yaratma imkanını yoxlamaq
$canCreate = $user->canCreateNewReferral();
if ($canCreate['can']) {
    // Yeni referral yarada bilər
} else {
    $reason = $canCreate['reason']; // Səbəb: 'daily_limit_exceeded' və ya 'referral_link_expired'
}

// Referral statistikasını əldə etmək
$stats = $user->getReferralStats();

// Bonus əlavə etmək
$user->addReferralBonus(
    amount: 10.00,
    reason: "Dostunu dəvət et kampaniyası"
);
```

## 🌱 Seeding Sistemi

### ReferralSeeder
Test datası yaradır:

```bash
php artisan db:seed --class=ReferralSeeder
```

**Yaradılan məlumatlar:**
- Seçilmiş istifadəçilər üçün referral kodları
- Hər istifadəçi üçün 1-5 arası random dəvət
- Status bölgüsü: 60% completed, 30% pending, 10% rejected
- Tamamlanmış referrallar üçün bonus balansları

**Nümunə statistika:**
```
- 10 dəvət edən istifadəçi
- 30-50 dəvət edilmiş istifadəçi
- 60% tamamlanmış referral
- 30% gözləyən referral
- 10% rədd edilmiş referral
```

## 🔍 Filter Sistemi

### ReferralFilter
```php
// Mövcud filterlər
'status' => 'pending|completed|rejected'  # Status filter
'referrer' => 'user_name'  # Dəvət edənin adı
'referral_code' => 'code'  # Referral kodu
```

### Filter istifadəsi:
```bash
# Pending statusundakı referrallar
GET /api/admin/referrals?status=pending

# Müəyyən istifadəçinin referralları
GET /api/admin/referrals?referrer=ali_memmedov

# Müəyyən kod ilə əlaqəli referrallar
GET /api/admin/referrals?referral_code=ABC123
```

## 🔔 Bildiriş Sistemi

### Avtomatik Bildirişlər
```php
// Referral tamamlandıqda (dəvət edənə bildiriş)
$this->referrer->notify(
    type: NotificationTypeEnum::REFERRAL_COMPLETED,
    data: [
        'referee_name' => $this->referee->fullname,
        'amount' => $this->referrer_bonus
    ]
);

// Referral rədd edildikdə
$this->referrer->notify(
    type: NotificationTypeEnum::REFERRAL_REJECTED,
    data: [
        'referee_name' => $this->referee->fullname,
        'reason' => $this->rejection_reason
    ]
);
```

## 🚀 Performance Optimizasiyası

### Repository Sorting
```php
// Status əsasında sıralama
$statusOrder = [
    ReferralStatusEnum::Pending,   // Əvvəlcə gözləyənlər
    ReferralStatusEnum::Completed, // Sonra tamamlananlar
    ReferralStatusEnum::Rejected   // Sonda rədd edilənlər
];

// Raw SQL ilə CASE operatorundan istifadə edərək custom sort yaradırıq
$orderByCase = "CASE status ";
foreach ($statusOrder as $index => $status) {
    $orderByCase .= "WHEN '$status' THEN $index ";
}
$orderByCase .= "END";

$query->orderByRaw($orderByCase);
```

### Statistika Hesablama
```php
// Effektiv SQL sorğuları
$systemStats = $query->selectRaw('
    COUNT(DISTINCT referrer_id) as active_referrers_count,
    COUNT(DISTINCT referee_id) as total_referees_count,
    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as successful_count,
    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected_count,
    COUNT(*) as total_count
', [
    ReferralStatusEnum::Completed,
    ReferralStatusEnum::Rejected
])->first();
```

## 🧪 Testing

### Unit Test Nümunəsi
```php
public function test_can_create_referral()
{
    $referrer = User::factory()->create();
    $referee = User::factory()->create();
    
    $referralService = app(ReferralService::class);
    $referral = $referralService->createReferral($referrer, $referee);
    
    $this->assertEquals('pending', $referral->status);
    $this->assertEquals($referrer->id, $referral->referrer_id);
    $this->assertEquals($referee->id, $referral->referee_id);
}

public function test_referral_completion_adds_bonus()
{
    $referrer = User::factory()->create(['referral_balance' => 0]);
    $referee = User::factory()->create(['referral_balance' => 0]);
    
    $referral = Referral::factory()->create([
        'referrer_id' => $referrer->id,
        'referee_id' => $referee->id,
        'referrer_bonus' => 10.00,
        'referee_bonus' => 5.00,
        'status' => ReferralStatusEnum::Pending
    ]);
    
    $referral->markAsCompleted();
    
    $this->assertEquals(10.00, $referrer->fresh()->referral_balance);
    $this->assertEquals(5.00, $referee->fresh()->referral_balance);
    $this->assertEquals(ReferralStatusEnum::Completed, $referral->fresh()->status);
}
```

## 🔧 Konfiqurasiya

### Referral Settings
```php
// config/settings.php (və ya Settings cədvəli)
'referral' => [
    'rewards' => [
        'referrer_amount' => 10.00,  // Dəvət edənin qazancı
        'referee_amount' => 5.00     // Dəvət olunanın qazancı
    ],
    'listing_rewards' => [
        'enabled' => true,
        'thresholds' => [
            5 => 10.00,   // 5 elan: 10 AZN bonus
            10 => 20.00,  // 10 elan: 20 AZN bonus
            20 => 50.00   // 20 elan: 50 AZN bonus
        ]
    ],
    'system' => [
        'expire_days' => 30,  // Referral link müddəti (gün)
        'max_referrals_per_day' => 10  // Gündəlik maksimum dəvət
    ]
]
```

## 📊 Admin Dashboard

Admin panel üçün əsas bölmələr:

1. **Ümumi Göstəricilər**
    - Aktiv dəvət edənlər sayı
    - Dəvət olunanların sayı
    - Uğur dərəcəsi (%)
    - Rədd dərəcəsi (%)

2. **Maliyyə Göstəriciləri**
    - Ümumi ödənilmiş bonus
    - Gözləyən ödənişlər
    - Orta bonus məbləği

3. **Zaman Göstəriciləri**
    - Bugün: dəvət sayı, bonus məbləği
    - Bu həftə: dəvət sayı, bonus məbləği
    - Bu ay: dəvət sayı, bonus məbləği

4. **Top Dəvət Edənlər**
    - Ən çox dəvət göndərən istifadəçilər
    - Dəvət sayları və qazancları

5. **Ətraflı Referral Siyahısı**
    - Status filtri
    - İstifadəçi filtri
    - Referral kodu filtri
    - Toplu əməliyyatlar (status dəyişmə, silmə)

## 🔄 Yenilənmə Qeydləri

**v1.0.0** (2025-01-15)
- İlkin versiya
- Referral kod sistemi
- Status workflow
- Bonus hesablama
- Admin panel

## 📞 Dəstək

Əlavə suallar və texniki dəstək üçün:
- Email: [support@example.com](mailto:support@example.com)
- Issue tracker: [Github Issues](https://github.com/example/referral-service/issues)

## 📄 Lisenziya

Bu layihə MIT lisenziyası altında lisenziyalanıb - ətraflı məlumat üçün [LICENSE](LICENSE) faylına baxın.
