# Voting System Service - README

## 📋 Ümumi Məlumat

Laravel 12 əsaslı rəy və qiymətləndirmə sistemi. Müştərilərin müxtəlif meyarlara görə xidmət və məhsullara rəy verməsini təmin edir. Çoxdilli dəstək və kateqoriya/şirkət əlaqələri daxildir.

### Əsas məqsəd:
- Müştəri rəy və qiymətləndirmə sistemi
- Müxtəlif qiymətləndirmə meyarları
- Kateqoriya və şirkət əsaslı voting
- Çoxdilli voting sistemləri

## 🎯 Əsas Xüsusiyyətlər

- ✅ **Çoxdilli Dəstək** - Azərbaycan, İngilis, Rus dilləri
- ✅ **Flexible Meyarlar** - Keyfiyyət, qiymət, çatdırılma və s.
- ✅ **Category Relations** - Hər kateqoriya üçün xüsusi meyarlar
- ✅ **Company Relations** - Şirkət əsaslı qiymətləndirmə
- ✅ **Vote Tracking** - Verilmiş rəylərin izlənməsi
- ✅ **Admin Management** - Voting meyarlarının idarəetməsi

## 🏗️ Fayl Strukturu

```
app/
├── Services/Module/VotingSystemService.php     # Biznes məntiq
├── Repositories/Module/VotingSystemRepository.php # Database əməliyyatları
├── Models/VotingSystem.php                     # Voting model və əlaqələr
├── Http/Controllers/Api/Admin/VotingSystemController.php # Admin API
└── Services/Filter/VotingSystemFilter.php     # Filter sistemi

database/
├── migrations/create_voting_systems_table.php # Database strukturu
└── seeders/VotingSystemSeeder.php             # Default voting meyarları
```

## 📊 Database Strukturu

### Voting Systems Table
```sql
CREATE TABLE voting_systems (
    id BIGINT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE,           # Unikal identifikator
    slug VARCHAR(255) UNIQUE,          # URL-friendly ad
    translates JSON NOT NULL,          # Çoxdilli adlar
    custom_field JSON,                 # Əlavə məlumatlar
    is_active BOOLEAN DEFAULT TRUE,    # Aktiv/deaktiv status
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### İlişkili Pivot Table-lar
```sql
-- Kateqoriya-Voting əlaqəsi
CREATE TABLE category_voting_systems (
    category_id BIGINT,
    voting_system_id BIGINT,
    PRIMARY KEY (category_id, voting_system_id)
);

-- Şirkət-Voting əlaqəsi  
CREATE TABLE company_voting_systems (
    company_id BIGINT,
    voting_system_id BIGINT,
    PRIMARY KEY (company_id, voting_system_id)
);

-- Voting nəticələri
CREATE TABLE listing_votes (
    id BIGINT PRIMARY KEY,
    listing_id BIGINT,                 # Hansı elan/məhsul
    voting_system_id BIGINT,           # Hansı meyar
    user_id BIGINT,                    # Kim rəy verib
    rating INTEGER,                    # Rəy (1-5 ulduz)
    comment TEXT,                      # Şərh
    created_at TIMESTAMP
);
```

### Çoxdilli JSON Strukturu
```json
{
    "az": {
        "name": "Xidmətin keyfiyyəti"
    },
    "en": {
        "name": "Service quality"
    },
    "ru": {
        "name": "Качество обслуживания"
    }
}
```

## 🔧 Service Metodları

### VotingSystemService
```php
// BaseCrudService-dən miras alınan metodlar
create(array $data)                    # Yeni voting meyarı yaratmaq
update(int $id, array $data)           # Voting meyarını yeniləmək
delete(int $id)                        # Voting meyarını silmək
findById(int $id)                      # ID ilə tapmaq
paginateAndFilter()                    # Filtrlənmiş siyahı
```

### İstifadə nümunələri:
```php
$votingService = app(VotingSystemService::class);

// Yeni voting meyarı yaratmaq
$voting = $votingService->create([
    'translates' => [
        'az' => ['name' => 'Təmizlik'],
        'en' => ['name' => 'Cleanliness'],
        'ru' => ['name' => 'Чистота']
    ],
    'is_active' => true
]);

// Aktiv voting meyarlarını əldə etmək
$activeVotings = VotingSystem::where('is_active', true)->get();
```

## 🔗 API Endpoints

### Admin Panel
```http
GET    /api/admin/voting-systems       # Voting meyarlarının siyahısı
POST   /api/admin/voting-systems       # Yeni voting meyarı yaratma
GET    /api/admin/voting-systems/{id}  # Voting meyarının detalları
PUT    /api/admin/voting-systems/{id}  # Voting meyarını yeniləmə
DELETE /api/admin/voting-systems/{id}  # Voting meyarını silmə
```

### API İstifadə Nümunələri

#### Voting meyarlarının siyahısı
```bash
curl -X GET "/api/admin/voting-systems" \
-H "Authorization: Bearer TOKEN" \
-H "Accept: application/json"
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "slug": "xidmetin-keyfiyyeti",
            "name": "Xidmətin keyfiyyəti",
            "is_active": true,
            "votes_count": 245,
            "average_rating": 4.2,
            "created_at": "2025-01-15T12:00:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 5
    }
}
```

#### Yeni voting meyarı yaratmaq
```bash
curl -X POST "/api/admin/voting-systems" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "translates": {
        "az": {
            "name": "Gözləmə müddəti"
        },
        "en": {
            "name": "Waiting time"
        },
        "ru": {
            "name": "Время ожидания"
        }
    },
    "custom_field": {
        "icon": "clock",
        "color": "#ff6b6b",
        "description": "Xidmət gözləmə müddəti"
    },
    "is_active": true
}'
```

#### Voting meyarını yeniləmək
```bash
curl -X PUT "/api/admin/voting-systems/1" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "translates": {
        "az": {
            "name": "Xidmətin keyfiyyəti (yeniləndi)"
        }
    },
    "is_active": false
}'
```

## 📋 Model Əlaqələri və Metodlar

### VotingSystem Model
```php
class VotingSystem extends BaseModel
{
    // Çoxdilli dəstək
    use HasTranslate;
    
    // Voting-ə verilmiş rəylər
    public function votes(): HasMany
    {
        return $this->hasMany(ListingVote::class);
    }
    
    // Voting sisteminin aid olduğu kateqoriyalar
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_voting_systems');
    }
    
    // Voting sisteminin aid olduğu şirkətlər  
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_voting_systems');
    }
}
```

### İstifadə nümunələri:
```php
$voting = VotingSystem::find(1);

// Bu meyara verilmiş bütün rəylər
$votes = $voting->votes()->with('user', 'listing')->get();

// Bu meyarın aid olduğu kateqoriyalar
$categories = $voting->categories;

// Ortalama reytinq
$averageRating = $voting->votes()->avg('rating');

// Rəy sayı
$voteCount = $voting->votes()->count();

// Müəyyən bir kateqoriyaya voting meyarı əlavə etmək
$category = Category::find(1);
$category->votingSystems()->attach([1, 2, 3]);
```

## 🌱 Seeding Sistemi

### VotingSystemSeeder
Sistemə default voting meyarlarını əlavə edir:

```bash
php artisan db:seed --class=VotingSystemSeeder
```

**Yaradılan default meyarlar:**
- **Xidmətin keyfiyyəti** - Müştəri xidməti qiymətləndirməsi
- **Çatdırılma** - Çatdırılma sürəti və keyfiyyəti
- **Qiymət** - Qiymət/keyfiyyət nisbəti
- **Məhsulun keyfiyyəti** - Məhsulun fiziki keyfiyyəti
- **Kredit şərtləri** - Ödəniş şərtləri və kredit imkanları

Hər meyar 3 dildə (AZ, EN, RU) yaradılır.

## 🔍 Filter Sistemi

### VotingSystemFilter
```php
// Axtarış filtri
'search' => 'search_term'              # Ad əsasında axtarış

// Searchable sahələr
protected function getSearchableFields(): array
{
    return ['name'];  // Çoxdilli adlarda axtarış
}
```

### Filter istifadəsi:
```bash
# Adda axtarış
GET /api/admin/voting-systems?search=keyfiyyət

# Aktiv olanları filtirləmə
GET /api/admin/voting-systems?is_active=true
```

## 🎯 Praktik İstifadə Ssenariləri

### 1. E-ticarət Rəy Sistemi
```php
// Məhsul səhifəsində voting meyarlarını göstərmək
$product = Product::find(1);
$category = $product->category;
$votingCriteria = $category->votingSystems()->active()->get();

foreach ($votingCriteria as $criteria) {
    echo $criteria->name; // "Məhsulun keyfiyyəti"
    // 5 ulduzlu reytinq sistemi
    $averageRating = $criteria->votes()
        ->where('listing_id', $product->id)
        ->avg('rating');
}
```

### 2. Şirkət Qiymətləndirməsi
```php
// Şirkətin bütün voting meyarları
$company = Company::find(1);
$companyVotings = $company->votingSystems;

foreach ($companyVotings as $voting) {
    $stats = [
        'name' => $voting->name,
        'total_votes' => $voting->votes()->count(),
        'average_rating' => $voting->votes()->avg('rating'),
        'latest_comments' => $voting->votes()
            ->whereNotNull('comment')
            ->latest()
            ->take(5)
            ->get()
    ];
}
```

### 3. Kateqoriya əsaslı Voting
```php
// Hər kateqoriya üçün fərqli voting meyarları
$electronics = Category::where('slug', 'elektronika')->first();
$electronics->votingSystems()->attach([1, 2, 4]); // Keyfiyyət, Çatdırılma, Qiymət

$food = Category::where('slug', 'yemek')->first();  
$food->votingSystems()->attach([1, 2, 6]); // Keyfiyyət, Çatdırılma, Dad
```

## 🧪 Testing

### Unit Test Nümunəsi
```php
public function test_can_create_voting_system()
{
    $data = [
        'translates' => [
            'az' => ['name' => 'Test Voting'],
            'en' => ['name' => 'Test Voting EN']
        ],
        'is_active' => true
    ];
    
    $voting = $this->votingService->create($data);
    
    $this->assertEquals('Test Voting', $voting->name);
    $this->assertTrue($voting->is_active);
    $this->assertDatabaseHas('voting_systems', [
        'is_active' => true
    ]);
}

public function test_voting_system_relationships()
{
    $voting = VotingSystem::factory()->create();
    $category = Category::factory()->create();
    
    // Əlaqə yaratmaq
    $voting->categories()->attach($category->id);
    
    $this->assertTrue($voting->categories->contains($category));
    $this->assertTrue($category->votingSystems->contains($voting));
}

public function test_can_calculate_average_rating()
{
    $voting = VotingSystem::factory()->create();
    $listing = Listing::factory()->create();
    
    // Test rəyləri yaratmaq
    ListingVote::factory()->create([
        'voting_system_id' => $voting->id,
        'listing_id' => $listing->id,
        'rating' => 5
    ]);
    
    ListingVote::factory()->create([
        'voting_system_id' => $voting->id,
        'listing_id' => $listing->id,  
        'rating' => 3
    ]);
    
    $average = $voting->votes()->avg('rating');
    $this->assertEquals(4.0, $average);
}
```

### API Test
```php
public function test_admin_can_manage_voting_systems()
{
    $admin = User::factory()->admin()->create();
    
    $response = $this->actingAs($admin)
        ->postJson('/api/admin/voting-systems', [
            'translates' => [
                'az' => ['name' => 'Yeni Meyar'],
                'en' => ['name' => 'New Criteria']
            ]
        ]);
        
    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'is_active']
        ]);
}
```

## 🔧 Validation Rules

### Voting System yaratma/yeniləmə:
```php
'translates' => 'required|array'                    # Tərcümələr mütləq
'translates.*' => 'required'                        # Hər dil tələb olunur
'translates.*.name' => 'required|string|max:255'    # Ad mütləq
'is_active' => 'boolean'                            # Status opsional
'custom_field' => 'nullable|array'                  # Əlavə sahələr opsional
```

### Xəta mesajları:
```php
'translates.required' => 'Tərcümələr sahəsi tələb olunur'
'translates.az.name.required' => 'Azərbaycan dilində ad mütləqdir'
'translates.*.name.max' => 'Ad maksimum 255 simvol ola bilər'
```

## 📊 Statistika və Analitika

### Voting Statistikaları
```php
class VotingSystemService 
{
    public function getStatistics(int $votingId): array
    {
        $voting = VotingSystem::find($votingId);
        
        return [
            'total_votes' => $voting->votes()->count(),
            'average_rating' => round($voting->votes()->avg('rating'), 2),
            'rating_distribution' => [
                '5_star' => $voting->votes()->where('rating', 5)->count(),
                '4_star' => $voting->votes()->where('rating', 4)->count(),
                '3_star' => $voting->votes()->where('rating', 3)->count(),
                '2_star' => $voting->votes()->where('rating', 2)->count(),
                '1_star' => $voting->votes()->where('rating', 1)->count(),
            ],
            'recent_trend' => $this->getRecentTrend($voting),
            'top_categories' => $this->getTopCategories($voting),
            'monthly_stats' => $this->getMonthlyStats($voting)
        ];
    }
    
    private function getRecentTrend(VotingSystem $voting): array
    {
        // Son 30 gün vs əvvəlki 30 gün
        $recent = $voting->votes()
            ->where('created_at', '>=', now()->subDays(30))
            ->avg('rating');
            
        $previous = $voting->votes()
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->avg('rating');
            
        return [
            'current_average' => round($recent, 2),
            'previous_average' => round($previous, 2),
            'trend' => $recent > $previous ? 'up' : ($recent < $previous ? 'down' : 'stable')
        ];
    }
}
```

## 🚀 Performance Optimizasiyası

### Database İndekslər
```sql
CREATE INDEX idx_voting_active ON voting_systems(is_active);
CREATE INDEX idx_voting_slug ON voting_systems(slug);
CREATE INDEX idx_votes_voting_listing ON listing_votes(voting_system_id, listing_id);
CREATE INDEX idx_votes_rating ON listing_votes(rating);
CREATE INDEX idx_votes_created ON listing_votes(created_at);
```

### Cache Strategiyası
```php
// Aktiv voting meyarları (1 saat cache)
Cache::remember('active_voting_systems', 3600, function () {
    return VotingSystem::where('is_active', true)
        ->with('translations')
        ->get();
});

// Kateqoriya voting meyarları (30 dəqiqə cache)
Cache::remember("category_voting_{$categoryId}", 1800, function () use ($categoryId) {
    return Category::find($categoryId)
        ->votingSystems()
        ->active()
        ->get();
});
```

## 📋 İcazə Sistemi

**Tələb olunan icazələr:**
- `voting_system_read` - Voting meyarlarını görüntüləmə
- `voting_system_create` - Yeni voting meyarı yaratma
- `voting_system_update` - Voting meyarını yeniləmə
- `voting_system_delete` - Voting meyarını silmə

## 🔄 Yenilənmə Qeydləri

**v1.0.0** (2025-01-15)
- İlkin versiya
- Çoxdilli voting sistem dəstəyi
- Kateqoriya və şirkət əlaqələri
- Əsas CRUD əməliyyatları
- Default voting meyarları
