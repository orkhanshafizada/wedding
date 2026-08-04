# Listing Management Service - README

## 📋 Ümumi Məlumat

Laravel 12 əsaslı hərtərəfli elan idarəetmə sistemi. Advanced filtering, multi-media dəstəyi, payment services, voting sistemi və təfsilatılı analytics ilə təchiz olunmuş enterprise səviyyəli marketplace platforması.

### Əsas məqsəd:
- Çoxfunksional elan yaradılması və idarəetməsi
- Premium xidmətlər sistemi
- Advanced search və filter
- Comprehensive analytics
- Multi-media management
- Location-based services

## 🎯 Əsas Xüsusiyyətlər

- ✅ **Advanced Listing Creation** - Multi-step listing creation process
- ✅ **Premium Services** - VIP, Premium, Bump, Color Frame, Large Frame
- ✅ **Smart Search & Filter** - Location, price, attributes, radius-based
- ✅ **Multi-Media Support** - Images, videos, galleries
- ✅ **Voting System** - Category-based rating system
- ✅ **Analytics Engine** - Comprehensive view tracking və analytics
- ✅ **Price History** - Automatic price change tracking
- ✅ **Favorites Management** - User-friendly favorites system
- ✅ **Status Workflow** - Pending → Active → Expired workflow
- ✅ **Image Management** - Multiple sizes, watermarks, thumbnails
- ✅ **Attribute System** - Dynamic attributes per category
- ✅ **Location Services** - Country, city, region, subway integration

## 🏗️ Sistem Strukturu

### Əsas Komponentlər
```
app/
├── Services/Module/
│   ├── ListingService.php              # Core business logic
│   ├── ListingVoteService.php          # Voting management
│   └── ListingHelpers/
│       └── ListingTitleGenerator.php   # Auto title generation
├── Repositories/Module/
│   ├── ListingRepository.php           # Data access layer
│   └── ListingVoteRepository.php       # Vote data management
├── Models/
│   ├── Listing.php                     # Main listing model
│   ├── ListingImage.php                # Image management
│   ├── ListingPaymentService.php       # Premium services
│   ├── ListingAnalytics.php            # Analytics data
│   ├── ListingView.php                 # View tracking
│   ├── ListingFavorite.php             # Favorites management
│   ├── ListingVote.php                 # Voting system
│   ├── ListingAttributeValue.php       # Attribute values
│   └── ListingPriceHistory.php         # Price tracking
├── Http/Controllers/Api/
│   ├── Admin/ListingController.php     # Admin management
│   └── Front/ListingController.php     # Public API
├── Enums/
│   ├── ListingStatusEnum.php           # Status definitions
│   └── PaymentServiceKeyEnum.php       # Premium service types
└── Services/Filter/
    └── ListingFilter.php               # Advanced filtering
```

## 📊 Database Strukturu

### Listings Table (Ana Elan Cədvəli)
```php
Schema::create('listings', function (Blueprint $table) {
    $table->id();
    $table->key();                       # UUID
    $table->code();                      # EL123456 formatında kod
    $table->slug();                      # SEO URL
    
    // Əlaqələr
    $table->foreignId('user_id');        # Elan sahibi
    $table->foreignId('category_id');    # Kateqoriya
    $table->foreignId('company_id')->nullable(); # Şirkət elanı
    
    // Lokasiya
    $table->foreignId('country_id');
    $table->foreignId('city_id');
    $table->foreignId('region_id')->nullable();
    $table->foreignId('subway_id')->nullable();
    
    // Məzmun
    $table->string('name')->nullable();   # Avtomatik generasiya
    $table->text('description');
    $table->text('video_link')->nullable();
    
    // JSON məlumatlar
    $table->json('contact');             # Telefon, email, whatsapp
    $table->json('location');            # Ünvan və koordinatlar
    
    // Qiymət
    $table->decimal('price', 15, 2)->nullable();
    $table->decimal('old_price', 15, 2)->nullable();
    $table->string('currency', 10);
    
    // Xüsusiyyətlər
    $table->boolean('is_new')->default(false);
    $table->boolean('is_negotiable')->default(false);
    $table->boolean('is_exchange')->default(false);
    $table->boolean('is_credit')->default(false);
    $table->boolean('is_delivery')->default(false);
    $table->boolean('show_price')->default(true);
    
    // Status və moderasiya
    $table->enum('status', ListingStatusEnum::getValues());
    $table->text('rejection_reason')->nullable();
    $table->timestamp('moderated_at')->nullable();
    $table->foreignId('moderated_by')->nullable();
    
    // Vaxt idarəetməsi
    $table->timestamp('published_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamp('last_bump_at')->nullable();
    
    // Aksiya sistemi
    $table->boolean('is_action')->default(false);
    $table->timestamp('action_start_date')->nullable();
    $table->timestamp('action_end_date')->nullable();
    
    // Statistika
    $table->integer('views_count')->default(0);
    $table->integer('favorites_count')->default(0);
    $table->integer('calls_count')->default(0);
    $table->integer('messages_count')->default(0);
    $table->decimal('search_score', 5, 2)->default(0);
    
    $table->customField();
    $table->timestamps();
    $table->softDeletes();
});
```

### Əlaqəli Cədvəllər
```php
// Şəkillər
listing_images: photo_path, order, is_main, alt_text

// Premium xidmətlər
listing_payment_services: payment_service_id, started_at, expires_at, usage_count

// Atribut dəyərləri
listing_attribute_values: attribute_id, attribute_option_id, value

// Baxış izləmə
listing_views: ip_address, device_info, location_info, duration

// Analytics
listing_analytics: views_data, interaction_data, search_data, price_analytics

// Favoritlər
listing_favorites: user_id, custom_fields (qeydlər, bildirişlər)

// Qiymət tarixçəsi
listing_price_histories: old_price, new_price, meta_data

// Səsverə
listing_votes: voting_system_id, rating, comment
```

## 🔧 Service Metodları

### ListingService
```php
// CRUD əməliyyatları
createListing($request)                # Yeni elan yaratmaq
update(int $id, array $data)           # Elan yeniləmək
delete(int $id): bool                  # Elan silmək

// Xüsusi yaradılma prosesi
saveAttributeValues($request, Listing) # Atribut dəyərlərini saxlamaq
uploadListingImages($request, Listing) # Şəkilləri yükləmək
setListingStatus(Listing)              # Status təyin etmək
setListingName(Listing)                # Avtomatik başlıq generasiyası

// Toplu əməliyyatlar
bulkUpdateStatus($data)                # Toplu status dəyişikliyi
bulkDelete($ids)                       # Toplu silmə

// Dashboard və analytics
getDashboardData(): array              # Admin dashboard məlumatları
getStats(): array                      # Ümumi statistika
getCategoryStats(): array              # Kateqoriya statistikası
getPremiumStats(): array               # Premium xidmət statistikası
getLocationStats(): array              # Lokasiya statistikası
getAnalyticsSummary(): array           # Analytics xülasəsi
```

### ListingVoteService
```php
vote(array $data, Listing $listing)   # Elan üçün səs vermək
getVotingSystems(Listing $listing)     # Elan üçün voting sistemləri
getListingVotes(Listing $listing)      # Elan səsləri
getUserVote(Listing, $systemId)        # İstifadəçi səsi
```

## 🚀 API Endpoints

### Public API
```http
# Elan axtarışı və siyahı
POST   /api/listings/search           # Advanced search
POST   /api/listings/by-uuids         # UUID-lər ilə siyahı
GET    /api/listings/{listing}/all    # Tam detallar

# Elan yaradılması
POST   /api/listings/create           # Yeni elan yaratmaq

# Elan əlaqəli məlumatlar
GET    /api/listings/{slug}/comments       # Şərhlər
GET    /api/listings/{slug}/related        # Oxşar elanlar
POST   /api/listings/{slug}/complaint      # Şikayət vermək

# Premium services
GET    /api/listings/payment-services      # Premium xidmət siyahısı
```

### Admin API
```http
# CRUD əməliyyatları
GET    /api/admin/listings            # Admin elan siyahısı
GET    /api/admin/listings/{id}       # Elan detalları
PUT    /api/admin/listings/{id}       # Elan yenilənməsi
DELETE /api/admin/listings/{id}       # Elan silmək

# Toplu əməliyyatlar
POST   /api/admin/listings/bulk-update-status  # Toplu status
POST   /api/admin/listings/bulk-delete         # Toplu silmə

# Dashboard
GET    /api/admin/listings/dashboard  # Admin dashboard
```

### Voting API
```http
# Listing voting
GET    /api/listing-votes/{slug}/voting-systems # Voting sistemləri
GET    /api/listing-votes/{slug}/votes          # Elan səsləri
GET    /api/listing-votes/{slug}/user-vote      # İstifadəçi səsi
POST   /api/listing-votes/{slug}/vote           # Səs vermək
```

## 💻 API İstifadə Nümunələri

### Elan yaratmaq
```bash
curl -X POST "/api/listings/create" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: multipart/form-data" \
-F "category_id=8" \
-F "description=2020-ci il BMW X5, ideal vəziyyətdə..." \
-F "price=45000" \
-F "currency=AZN" \
-F "country_id=1" \
-F "city_id=1" \
-F "contact[name]=Əli Məmmədov" \
-F "contact[phones][]=+994501234567" \
-F "location[address]=Bakı şəhəri, Nərimanov rayonu" \
-F "location[coordinates][latitude]=40.409264" \
-F "location[coordinates][longitude]=49.867092" \
-F "images[]=@photo1.jpg" \
-F "images[]=@photo2.jpg" \
-F "attributes[1]=5" \
-F "attributes[2]=12" \
-F "is_new=false" \
-F "is_credit=true"
```

### Advanced Search
```bash
curl -X POST "/api/listings/search" \
-H "Content-Type: application/json" \
-d '{
    "category_id": 8,
    "min_price": 25000,
    "max_price": 75000,
    "city_id": 1,
    "is_credit": true,
    "attributes": {
        "1": 5,
        "2": [12, 15],
        "3": "2018-2022"
    },
    "radius": {
        "lat": 40.3792,
        "lng": 49.8473,
        "distance": 5
    },
    "sort": "price_asc",
    "limit": 20
}'
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "code": "EL-00001",
            "name": "BMW X5 2020, 3.0 Dizel, Bakı",
            "slug": "bmw-x5-2020-3-0-dizel-baki",
            "price": 45000,
            "currency": "AZN",
            "is_vip": true,
            "is_premium": false,
            "main_photo_url": "https://example.com/uploads/listing/photo.jpg",
            "location": {
                "address": "Bakı şəhəri, Nərimanov rayonu"
            },
            "published_at": "2025-01-15T12:00:00Z",
            "views_count": 150,
            "favorites_count": 12
        }
    ],
    "total": 1
}
```

### Səs vermək
```bash
curl -X POST "/api/listing-votes/bmw-x5-2020/vote" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "voting_system_id": 1,
    "rating": 4.5,
    "comment": "Çox yaxşı avtomobil, təmiz və qənaətlidir"
}'
```

## 📈 Status Workflow

### Elan Statusları
```php
enum ListingStatusEnum {
    PENDING = 'pending';        # Moderator təsdiqi gözləyir
    ACTIVE = 'active';          # Aktiv və görünən
    EXPIRED = 'expired';        # Müddəti bitib
    REJECTED = 'rejected';      # Moderator tərəfindən rədd
    SUSPENDED = 'suspended';    # Müvəqqəti dayandırılıb
    ARCHIVED = 'archived';      # İstifadəçi tərəfindən arxivləşdirilib
}
```

### Status Dəyişiklik Prosesi
1. **PENDING** → Yeni yaradılmış elan (şirkət elanları avtomatik ACTIVE)
2. **ACTIVE** → Admin təsdiqi + expires_at tarixi təyin
3. **EXPIRED** → Müddət bitdikdə avtomatik
4. **REJECTED** → Admin rəddi + rejection_reason
5. **SUSPENDED** → Qaydaları pozduqda
6. **ARCHIVED** → İstifadəçi tərəfindən

## 🎯 Premium Services Sistemi

### Premium Service Növləri
```php
enum PaymentServiceKeyEnum {
    VIP = 'vip';                # Üst sıralarda göstərmə
    PREMIUM = 'premium';        # Xüsusi işarələmə
    BUMP = 'bump';             # İrəli çəkmə (usage-based)
    COLOR_FRAME = 'color_frame'; # Rəngli çərçivə
    LARGE_FRAME = 'large_frame'; # Böyük çərçivə (x2)
}
```

### Premium Service Yoxlamaları
```php
// Listing model-də
$listing->isVip();              # VIP olub-olmadığı
$listing->isPremium();          # Premium olub-olmadığı
$listing->isBumped();           # İrəli çəkilib-çəkilmədiyi
$listing->isColorFrame();       # Rəngli çərçivə var-yox
$listing->isLargeFrame();       # Böyük çərçivə var-yox

// Scope-larla filtrleme
Listing::vip()->get();          # VIP elanlar
Listing::premium()->get();      # Premium elanlar
Listing::bumped()->get();       # İrəli çəkilmiş elanlar
```

### Premium Service İdarəetmə
```php
class ListingPaymentService {
    // İstifadə limitləri
    public function hasReachedUsageLimit(): bool
    public function use(): bool              # İstifadə etmək
    
    // Vaxt idarəetməsi
    public function isStillValid(): bool
    public function extend(int $value): bool
    public function getRemainingHours(): int
    
    // Avtomatik yenilənmə
    public function canAutoRenew(): bool
}
```

## 🔍 Advanced Filter Sistemi

### Filter Parametrləri
```php
protected array $filters = [
    'search',              # Mətn axtarışı
    'category_id',         # Kateqoriya (alt kateqoriyalar daxil)
    'min_price',           # Minimum qiymət
    'max_price',           # Maksimum qiymət
    'currency',            # Valyuta
    'country_id',          # Ölkə
    'city_id',             # Şəhər
    'region_id',           # Rayon
    'subway_id',           # Metro
    'is_new',              # Yeni/işlənmiş
    'is_credit',           # Kredit imkanı
    'is_delivery',         # Çatdırılma
    'is_negotiable',       # Razılaşma ilə
    'is_action',           # Endirimli
    'attributes',          # Dinamik atributlar
    'payment_service',     # Premium xidmətlər
    'radius',              # Radius əsaslı axtarış
    'published_at',        # Dərc tarixi
    'min_views',           # Minimum baxış sayı
    'is_expired'           # Müddəti bitmiş/bitməmiş
];
```

### Radius-based Search
```php
// 5 km radiusda axtarış
'radius' => [
    'lat' => 40.3792,
    'lng' => 49.8473,
    'distance' => 5
]

// Haversine formula ilə məsafə hesablanması
$haversine = "(
    6371 * acos(
        cos(radians($lat))
        * cos(radians(JSON_EXTRACT(location, '$.coordinates.latitude')))
        * cos(radians(JSON_EXTRACT(location, '$.coordinates.longitude')) - radians($lng))
        + sin(radians($lat))
        * sin(radians(JSON_EXTRACT(location, '$.coordinates.latitude')))
    )
)";
```

### Attribute-based Filtering
```php
// Dinamik atribut filterləri
'attributes' => [
    '1' => 5,              # Marka: BMW
    '2' => [12, 15],       # Model: X5, X6
    '3' => '2018-2022',    # İl aralığı
    '4' => 'qara'          # Rəng
]

// Çoxlu seçim dəstəyi
foreach ($attributes as $attributeId => $value) {
    if (is_array($value)) {
        // Multi-select
        $query->whereHas('attributes', function($q) use ($attributeId, $value) {
            $q->where('attribute_id', $attributeId)
                ->whereIn('attribute_option_id', $value);
        });
    }
}
```

## 📊 Analytics Sistemi

### ListingAnalytics Features
```php
class ListingAnalytics {
    // Baxış statistikası
    public function recordView(array $viewData): bool
    protected $views_data = [
        'total' => ['daily', 'weekly', 'monthly'],
        'sources' => ['search', 'direct', 'social'],
        'devices' => ['mobile', 'desktop', 'tablet']
    ];
    
    // İnteraksiya izləmə
    public function recordInteraction(string $type, array $data): bool
    protected $interaction_data = [
        'contact_views' => ['phone', 'whatsapp', 'message'],
        'engagement' => ['avg_view_time', 'bounce_rate'],
        'actions' => ['share', 'favorite', 'report']
    ];
    
    // Axtarış performansı
    public function updateSearchPerformance(array $data, string $eventType): bool
    protected $search_data = [
        'keywords' => ['bmw x5' => 50],
        'filters' => ['price_range', 'year'],
        'performance' => ['impressions', 'clicks', 'conversions']
    ];
    
    // Qiymət analizi
    public function updatePriceAnalytics(): bool
    protected $price_analytics = [
        'market_position' => ['category_avg', 'similar_avg'],
        'interest_by_price' => ['below_price', 'at_price', 'above_price']
    ];
}
```

## 🎨 Image Management

### Image Konfiqurasiyası
```php
public function getImageFields(): array
{
    return [
        'photo_path' => [
            'path' => 'listing',
            'thumbnail' => [200, 200],     # Kiçik ölçü
            'medium' => [800, 600],        # Orta ölçü
            'large' => [1600, 1200],       # Böyük ölçü
            'watermark' => true,           # Watermark
            'quality' => 85                # Keyfiyyət
        ]
    ];
}
```

### Image Operations
```php
class ListingImage {
    public function makeMain(): bool           # Əsas şəkil təyin etmək
    public function updateOrder(int $order)    # Sıralama dəyişmək
    public function updateSeoInfo(string $alt) # SEO update
    
    // Virtual attributes
    protected function url(): Attribute         # Orijinal URL
    protected function thumbnailUrl(): Attribute # Thumbnail URL
    protected function mediumUrl(): Attribute   # Medium URL
}
```

## 💖 Favorites Sistemi

### Favorites Management
```php
class ListingFavorite {
    // Qovluq sistemi
    public function setFolder(string $folderName): bool
    
    // Qeydlər sistemi
    public function updateNotes(string $notes): bool
    
    // Bildiriş tənzimləmələri
    public function updateNotificationSettings(array $settings): bool
    // ['price_drop' => true, 'expiring_soon' => true]
    
    // Teq sistemi
    public function updateTags(array $tags): bool
    // ['təcili', 'yaxşı qiymət', 'düşünülməli']
    
    // Scope-lar
    public function scopeInFolder($query, string $folder)
    public function scopeHasTag($query, string $tag)
    public function scopeWithNotifications($query)
}
```

## 🌱 Seeding Sistemi

### ListingSeeder
1000 elan və əlaqəli məlumatları yaradır:

```bash
php artisan db:seed --class=ListingSeeder
```

**Yaradılan məlumatlar:**
- **1000 elan**: Müxtəlif statuslarda
- **8000+ şəkil**: Hər elana 1-8 şəkil
- **3000+ atribut dəyəri**: Realistic attribute values
- **Premium services**: 20% VIP, 30% Premium, 40% Bump
- **Views və analytics**: Realistic view patterns
- **Favorites**: User favorite patterns
- **Price history**: Qiymət dəyişikliyi tarixçəsi

## 🔔 Notification System

### Auto Notifications
```php
// Status dəyişikliklərində
- ListingStatusEnum::ACTIVE → Təsdiq bildirişi
- ListingStatusEnum::REJECTED → Rədd bildirişi + səbəb
- ListingStatusEnum::EXPIRED → Müddət bitməsi

// Voting sistemində
- NotificationTypeEnum::NEW_VOTE → Yeni səs bildirişi

// Favorites-də
- Price drop notification → Qiymət düşməsi
- Expiring soon → Müddət bitməsi
```

## 🚀 Performance Optimizasiyası

### Database İndekslər
```php
// listings table
$table->index(['status', 'published_at']);
$table->index(['category_id', 'status']);
$table->index(['city_id', 'status']);
$table->index('search_score');

// JSON indexes
$table->rawIndex('(JSON_EXTRACT(location, "$.coordinates.latitude"))', 'location_lat');
$table->rawIndex('(JSON_EXTRACT(location, "$.coordinates.longitude"))', 'location_lng');
```

### Query Optimization
```php
// Eager loading
Listing::with([
    'mainImage',
    'category',
    'city',
    'attributes.attribute',
    'paymentServices.paymentService'
])->get();

// Chunk processing
Listing::chunk(100, function ($listings) {
    foreach ($listings as $listing) {
        // Process listings
    }
});
```

## 🧪 Testing Nümunələri

### Service Test
```php
public function test_can_create_listing_with_attributes()
{
    $user = User::factory()->create();
    $category = Category::factory()->create();
    
    $listingData = [
        'category_id' => $category->id,
        'description' => 'Test elan təsviri',
        'price' => 1000,
        'currency' => 'AZN'
    ];
    
    $listing = $this->listingService->createListing(
        new Request($listingData)
    );
    
    $this->assertEquals('pending', $listing->status);
    $this->assertNotNull($listing->code);
}
```

### API Test
```php
public function test_user_can_search_listings()
{
    Listing::factory()->count(10)->create();
    
    $response = $this->postJson('/api/listings/search', [
        'min_price' => 100,
        'max_price' => 1000
    ]);
    
    $response->assertOk()
        ->assertJsonStructure(['data', 'total']);
}
```

## 🔧 Validation Rules

### Elan Yaradılması
```php
'category_id' => 'required|exists:categories,id'
'description' => 'required|string|min:20|max:4000'
'price' => 'required_if:show_price,true|numeric|min:0'
'images' => 'required|array|min:1|max:20'
'images.*' => 'required|image|max:10240'  # 10MB limit
'contact.phones.*' => [AzerbaijanPhoneRule::mobile()]
'location.coordinates.latitude' => 'required|numeric'
'location.coordinates.longitude' => 'required|numeric'

// Dynamic attribute validation
'attributes.{attributeId}' => [
    AttributeTypeEnum::Select => 'exists:attribute_options,id',
    AttributeTypeEnum::MultiSelect => 'array',
    AttributeTypeEnum::Range => 'string',  # "min-max" format
    AttributeTypeEnum::Phone => [AzerbaijanPhoneRule::mobile()]
]
```

## 📋 İcazə Sistemi

**Tələb olunan icazələr:**
- `listing_read` - Elan siyahısını görüntüləmə
- `listing_create` - Yeni elan yaratma
- `listing_update` - Elan yenilənməsi
- `listing_delete` - Elan silmə
- `listing_status` - Status dəyişdirmə (admin)

---

**💡 Qeyd:** Sistem həm fərdi həm də şirkət elanlarını dəstəkləyir. Şirkət elanları avtomatik olaraq ACTIVE statusuna keçir, fərdi elanlar PENDING statusunda moderator təsdiqini gözləyir.
