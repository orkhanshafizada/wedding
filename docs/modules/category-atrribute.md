# Category and Attribute Management System Documentation

<img src="https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12.0"/>
<img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2"/>
<img src="https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge" alt="MIT License"/>

## 📋 Ümumi Məlumat

Laravel 12 əsaslı hərtərəfli kateqoriya və atribut idarəetmə sistemi. Hierarchical kateqoriya strukturu, çoxdilli atribut dəstəyi və dinamik filter imkanları təqdim edir. Elanlar, məhsullar və ya istənilən klassifikasiya olunmuş məzmun üçün ətraflı kategoriya-atribut əlaqəsi yaratmağa imkan verir.

### Əsas məqsəd:
- Çoxsəviyyəli kateqoriya strukturu yaratmaq
- Kateqoriyalara xüsusi atributlar əlavə etmək
- Atributları tip və mövqeyə görə təşkil etmək
- Dinamik filterlər və axtarış imkanları yaratmaq
- Admin paneldən tam idarəetmə

## 🎯 Əsas Xüsusiyyətlər

- ✅ **Hierarchical Kateqoriyalar** - Sonsuz səviyyəli kateqoriya strukturu
- ✅ **Çoxdilli Dəstək** - Bütün məlumatlar çoxdilli saxlanılır (az, en, vs.)
- ✅ **25+ Atribut Tipi** - Mətn, rəqəm, seçim, tarix, media və daha çox
- ✅ **Dinamik Seçimlər** - Asılı seçimlər (marka→model)
- ✅ **Kateqoriya-Atribut Əlaqəsi** - Kateqoriyalara xüsusi atribut dəstləri
- ✅ **Atribut Mövqeyi** - Atributların harada göstərilməsini təyin etmək
- ✅ **Validasiya Qaydaları** - Atributlar üçün xüsusi validasiya
- ✅ **Meta Tags** - SEO üçün kateqoriya meta məlumatları
- ✅ **Əlaqəli Kateqoriyalar** - Kateqoriyalar arası əlaqələr
- ✅ **Səsvermə Sistemləri** - Kateqoriya əsaslı səsvermə imkanları

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
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=AttributeSeeder

# Xidməti başlatmaq
php artisan serve
```

## 🏗️ Sistem Arxitekturası

Sistem Model-Service-Repository pattern əsasında qurulub:

```
Kontrollerlar ─► Servis ─► Repozitoriya ─► Model ─► Database
                   │             │
                   ▼             ▼
               Biznes Məntiq   Cache Mexanizmi
```

### Əsas komponentlər:

1. **Modellər**: Database strukturunu əks etdirir
2. **Repozitoriyalar**: Database əməliyyatlarını və cache mexanizmini idarə edir
3. **Servislər**: Biznes məntiqini ehtiva edir
4. **Kontrollerlər**: HTTP sorğularını qəbul edir və servislərə yönləndirir
5. **Enumlar**: Status, tip və digər sabit dəyərlər üçün

## 📊 Database Strukturu

### Categories Table
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->key(); // UUID yaradır
    $table->slug(); // URL-friendly string
    $table->parentId(); // Hierarchical struktur
    $table->unsignedBigInteger('terms_id')->nullable(); // Şərtlər və qaydalar
    $table->translates(); // Çoxdilli məlumatlar
    $table->string('icon')->nullable(); // Kateqoriya ikonu
    $table->photo(); // Kateqoriya şəkli
    $table->json('meta_tags')->nullable(); // SEO məlumatları
    $table->customField(); // Əlavə xüsusi məlumatlar
    $table->boolean('is_default')->default(false); // Default seçim
    $table->boolean('is_active')->default(true);  // Aktivlik statusu
    $table->integer('order')->default(0); // Sıralama
    $table->timestamps();
    $table->softDeletes();
    $table->index(['is_active', 'order', 'is_default']);
});
```

### Attributes Table
```php
Schema::create('attributes', function (Blueprint $table) {
    $table->id();
    $table->key(); // UUID
    $table->slug(); // URL-friendly string
    $table->json('translates')->nullable(); // Çoxdilli məlumatlar
    $table->string('group_name')->nullable(); // Admin qruplaşdırma
    $table->string('type'); // AttributeTypeEnum-dan tip
    $table->parentId(); // Asılılıq əlaqəsi
    $table->string('position')->nullable(); // AttributeTypePosition-dan
    $table->boolean('display_title_everywhere')->default(false); // Başlıq göstərilməsi
    $table->boolean('is_active')->default(true); // Aktivlik statusu
    $table->integer('order')->default(0); // Sıralama
    $table->customField(); // Əlavə xüsusi məlumatlar
    $table->timestamps();
    $table->softDeletes();
    $table->index(['type', 'is_active']);
    $table->index('order');
});
```

### Attribute Options Table
```php
Schema::create('attribute_options', function (Blueprint $table) {
    $table->id();
    $table->key(); // UUID
    $table->slug(); // URL-friendly string
    $table->foreignId('attribute_id')->constrained()->onDelete('cascade');
    $table->parentId(); // Asılı seçimlər üçün (məs. marka→model)
    $table->translates(); // Çoxdilli məlumatlar
    $table->customField(); // Əlavə xüsusi məlumatlar (icon, color, vs.)
    $table->boolean('is_default')->default(false); // Default seçim
    $table->boolean('is_active')->default(true); // Aktivlik statusu
    $table->integer('order')->default(0); // Sıralama
    $table->timestamps();
    $table->softDeletes();
    $table->index(['is_active', 'order']);
});
```

### Category Attribute Table (Pivot)
```php
Schema::create('category_attribute', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->onDelete('cascade');
    $table->foreignId('attribute_id')->constrained()->onDelete('cascade');
    $table->json('validation_rules')->nullable(); // Validasiya qaydaları
    $table->boolean('is_required')->default(false); // Məcburi sahə
    $table->boolean('is_visible')->default(true); // Görünən sahə
    $table->integer('order')->default(0); // Sıralama
    $table->customField(); // Əlavə xüsusi məlumatlar
    $table->timestamps();
    $table->unique(['category_id', 'attribute_id']); // Unikal əlaqə
    $table->index(['category_id', 'is_visible', 'order']);
});
```

### Əlavə Cədvəllər

```php
// Əlaqəli kateqoriyalar
Schema::create('category_related', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->foreignId('related_id')->constrained('categories')->cascadeOnDelete();
    $table->timestamps();
});

// Kateqoriya-səsvermə sistemi əlaqəsi
Schema::create('category_voting_systems', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->foreignId('voting_system_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
});
```

## 🧩 Modellər və Əlaqələr

### Category Model
```php
class Category extends BaseModel
{
    use HasImage, HasTranslate, HasSeoLink;

    // Əlaqələr
    public function parent(): BelongsTo
    public function children(): HasMany
    public function attributes(): HasMany  // CategoryAttribute
    public function votingSystems(): HasMany
    public function relateds(): HasMany
    public function terms(): BelongsTo
    public function listings(): HasMany
    public function sections(): BelongsToMany
    
    // Yardımçı metodlar
    public function syncRelations(array $data): void
    
    // Translation sahələri
    public function getTranslatableAttributes(): array {
        return ['name', 'description'];
    }
}
```

### Attribute Model
```php
class Attribute extends BaseModel
{
    use HasTranslate, HasLoggable;

    // Əlaqələr
    public function options(): HasMany
    public function categories(): BelongsToMany
    public function parent(): BelongsTo
    public function children(): HasMany
    
    // Virtual atributlar
    public function positionText(): AttributeCast
    public function typeText(): AttributeCast
    protected function hasDependentOptions(): AttributeCast
    
    // Translation sahələri
    public function getTranslatableAttributes(): array {
        return ['name', 'description', 'listing_name_prefix', 'listing_name_suffix'];
    }
}
```

### AttributeOption Model
```php
class AttributeOption extends Model
{
    use HasUuid, HasSlug, HasTranslate;

    // Əlaqələr
    public function parent(): BelongsTo
    public function children(): HasMany
    public function attribute(): BelongsTo
    
    // Translation sahələri
    public function getTranslatableAttributes(): array {
        return ['name'];
    }
}
```

### CategoryAttribute Model (Pivot)
```php
class CategoryAttribute extends Pivot
{
    public $incrementing = true;

    // Əlaqələr
    public function category(): BelongsTo
    public function attribute(): BelongsTo
}
```

## 🔧 Servis Metodları

### CategoryService
```php
// Əsas CRUD metodları (BaseCrudService-dən gəlir)
create(array $data)
update(int $id, array $data)
delete(int $id)
findById(int $id)

// Kateqoriya spesifik metodlar
getParentCategories(): Collection
getChildCategories(int $parentId): Collection
getCategoryWithAttributes(int $id)
attachAttribute(int $categoryId, array $data): Model
detachAttribute(int $categoryId, int $attributeId): bool
attributeOrder($id, $orders)
config($id)
configSave($id, array $data)
```

### AttributeService
```php
// Əsas CRUD metodları (BaseCrudService-dən gəlir)
create(array $data)
update(int $id, array $data)
delete(int $id)
findById(int $id)

// Atribut spesifik metodlar
addAttributeToCategory(int $categoryId, array $data): bool
removeAttributeFromCategory(int $categoryId, int $attributeId): bool
attributeOptions(int $attributeId)
syncAttributeOptions(int $attributeId, array $request)
deleteOption($id, $optionId)
orderOption($id, $optionId)
statusOption(int $id, int $orderId, array $request)

// Köməkçi metodlar
filters(): array
getAttributeTypes(): \Illuminate\Support\Collection
getAttributePositions(): \Illuminate\Support\Collection
```

## 🔢 Enumlar və Sabit Dəyərlər

### AttributeTypeEnum
```php
final class AttributeTypeEnum extends Enum
{
    // Mətn tipləri
    const Text = 'text';           // Sadə mətn sahəsi
    const Textarea = 'textarea';   // Uzun mətn sahəsi
    const Html = 'html';           // HTML redaktoru
    const Email = 'email';         // E-poçt sahəsi
    const Phone = 'phone';         // Telefon nömrəsi
    const Url = 'url';             // Web ünvanı

    // Rəqəm tipləri
    const Integer = 'integer';     // Tam rəqəm
    const Decimal = 'decimal';     // Kəsr rəqəm
    const Price = 'price';         // Qiymət sahəsi
    const Range = 'range';         // Aralıq seçimi

    // Seçim tipləri
    const Select = 'select';       // Tək seçim
    const MultiSelect = 'multi_select'; // Çoxlu seçim
    const Radio = 'radio';         // Radio düymələri
    const Checkbox = 'checkbox';   // Çoxlu seçim qutuları

    // Tarix və vaxt
    const Date = 'date';           // Tarix
    const Time = 'time';           // Vaxt
    const DateTime = 'datetime';   // Tarix və vaxt
    const Year = 'year';           // İl seçimi
    const Month = 'month';         // Ay seçimi

    // Media tipləri
    const Image = 'image';         // Tək şəkil
    const Gallery = 'gallery';     // Şəkil qalereyası
    const File = 'file';           // Fayl yükləmə

    // Xüsusi tiplər
    const Color = 'color';         // Rəng seçimi
    const Location = 'location';   // Yer/Ünvan seçimi
    const Boolean = 'boolean';     // Bəli/Xeyr seçimi
}
```

### AttributePositionEnum
```php
final class AttributePositionEnum extends Enum
{
    const OnlyMoreSearch = 'only-more-search'; // Yalnız "daha çox axtarış" bölməsində
    const OnlyListing = 'only-listing';        // Yalnız elan detallarında
    const EveryWhere = 'every-where';          // Hər yerdə (həm axtarış, həm də detallarda)
}
```

## 📝 Repozitoriya Metodları

### CategoryRepository
```php
// Əsas metodlar
fetchCategoryByParent()
getActiveParentCategories(): Collection
getActiveChildCategories(int $parentId): Collection
getCategoryWithAttributes(int $id)
getCategoryWithAttributesByUuid(string $uuid)
getAllParentCategories(): Collection
getAllCategories(): Collection

// Atribut əlaqəli metodlar
attachAttribute(int $categoryId, array $data): Model
detachAttribute(int $categoryId, int $attributeId): bool
attributeOrder(int $attributeId, array $orders): bool

// Konfiqurasiya metodları
config($id): array
configSave($id, array $data): true

// Cache köməkçiləri
executeWithCache(string $key, \Closure $callback)
```

### AttributeRepository
```php
// Kateqoriya əlaqəli metodlar
addToCategory(int $categoryId, array $data): bool
removeFromCategory(int $categoryId, int $attributeId): bool

// Option əlaqəli metodlar
syncOptions(int $attributeId, array $request)
deleteOption(int $attributeId, int $optionId): bool
orderOption(int $attributeId, array $orders): bool
statusOption(int $attributeId, int $orderId, array $request)
attributeOptions(int $attributeId)
getActiveOptions(int $attributeId): Collection
getDependentOptions(int $parentOptionId)

// Cache metodları
executeWithCache(string $key, \Closure $callback)
```

## 🔄 Filter Sistemləri

### CategoryFilter
```php
class CategoryFilter extends BaseFilter
{
    protected array $filters = [
        'search',  // Kateqoriya adında axtarış
        'parent_id', // Ana kateqoriyaya görə filter
    ];

    protected function filterParentId($query, $value)
    protected function getSearchableFields(): array
}
```

### AttributeFilter
```php
class AttributeFilter extends BaseFilter
{
    protected array $filters = [
        'search',    // Atribut adında axtarış
        'parent_id', // Asılı atributlar üçün
        'type',      // Atribut tipinə görə
        'position',  // Atribut mövqeyinə görə
    ];

    protected function filterParentId($query, $value)
    protected function filterType($query, $value)
    protected function filterPosition($query, $value)
    protected function getSearchableFields(): array
}
```

## 🛣️ API Endpoints

### Admin Panel API

#### Kateqoriyalar
```http
GET    /api/admin/categories           # Bütün kateqoriyalar
POST   /api/admin/categories           # Yeni kateqoriya yaratmaq
GET    /api/admin/categories/{id}      # Kateqoriya detalları
PUT    /api/admin/categories/{id}      # Kateqoriya yeniləmək
DELETE /api/admin/categories/{id}      # Kateqoriya silmək
GET    /api/admin/categories/{id}/attributes  # Kateqoriyanın atributları
POST   /api/admin/categories/{id}/attributes  # Kateqoriyaya atribut əlavə etmək
DELETE /api/admin/categories/{id}/attributes/{attributeId}  # Atribut silmək
POST   /api/admin/categories/{id}/attributes/order  # Atribut sıralaması
GET    /api/admin/categories/{id}/config  # Kateqoriya konfiqurasiyası
POST   /api/admin/categories/{id}/config-save  # Konfiqurasiya yeniləmək
```

#### Atributlar
```http
GET    /api/admin/attributes           # Bütün atributlar
POST   /api/admin/attributes           # Yeni atribut yaratmaq
GET    /api/admin/attributes/{id}      # Atribut detalları
PUT    /api/admin/attributes/{id}      # Atribut yeniləmək
DELETE /api/admin/attributes/{id}      # Atribut silmək
GET    /api/admin/attributes/{id}/options  # Atributun seçimləri
POST   /api/admin/attributes/{id}/options  # Seçim əlavə etmək/yeniləmək
DELETE /api/admin/attributes/{id}/options/{optionId}  # Seçim silmək
POST   /api/admin/attributes/{id}/options/order  # Seçim sıralaması
POST   /api/admin/attributes/{id}/options/{optionId}/status  # Seçim statusu
```

### Frontend API

#### Kateqoriyalar
```http
GET    /api/app/categories             # Ana kateqoriyalar
GET    /api/app/categories/{id}/children  # Alt kateqoriyalar
GET    /api/app/categories/{id}/attributes  # Kateqoriyanın atributları (elanlar üçün)
```

## 💻 Praktiki İstifadə Nümunələri

### Yeni Kateqoriya Yaratmaq
```php
$categoryService = app(CategoryService::class);

$category = $categoryService->create([
    'translates' => [
        'az' => [
            'name' => 'Elektronika',
            'description' => 'Elektronika məhsulları'
        ],
        'en' => [
            'name' => 'Electronics',
            'description' => 'Electronic products'
        ]
    ],
    'parent_id' => 0, // Ana kateqoriya
    'icon' => 'fas fa-laptop',
    'meta_tags' => [
        'title' => 'Elektronika',
        'description' => 'Elektronika məhsulları və aksesuarları',
        'keywords' => 'elektronika, telefon, kompüter'
    ],
    'is_active' => true,
    'order' => 1
]);
```

### Kateqoriyaya Atribut Əlavə Etmək
```php
$categoryService = app(CategoryService::class);

$categoryService->attachAttribute($categoryId, [
    'attribute_id' => $attributeId,
    'is_required' => true,
    'is_visible' => true,
    'validation_rules' => [
        'min' => 1,
        'max' => 100
    ],
    'custom_fields' => [
        'placeholder' => 'Məhsul adını daxil edin',
        'help_text' => 'Məhsulun tam adını yazın'
    ]
]);
```

### Atribut Yaratmaq
```php
$attributeService = app(AttributeService::class);

$attribute = $attributeService->create([
    'translates' => [
        'az' => [
            'name' => 'Rəng',
            'description' => 'Məhsulun rəngi'
        ],
        'en' => [
            'name' => 'Color',
            'description' => 'Product color'
        ]
    ],
    'type' => AttributeTypeEnum::Select,
    'position' => AttributePositionEnum::EveryWhere,
    'is_active' => true,
    'order' => 1
]);
```

### Atributa Seçimlər Əlavə Etmək
```php
$attributeService = app(AttributeService::class);

$attributeService->syncAttributeOptions($attributeId, [
    'translates' => [
        'az' => ['name' => 'Qırmızı'],
        'en' => ['name' => 'Red']
    ],
    'custom_fields' => [
        'color' => '#FF0000'
    ],
    'is_default' => false,
    'is_active' => true
]);
```

### Asılı Atributlar (Marka → Model)
```php
// 1. Marka atributu yaratmaq
$brandAttribute = $attributeService->create([
    'translates' => [
        'az' => ['name' => 'Marka'],
        'en' => ['name' => 'Brand']
    ],
    'type' => AttributeTypeEnum::Select,
    'position' => AttributePositionEnum::EveryWhere
]);

// 2. Model atributu yaratmaq (markaya bağlı)
$modelAttribute = $attributeService->create([
    'translates' => [
        'az' => ['name' => 'Model'],
        'en' => ['name' => 'Model']
    ],
    'type' => AttributeTypeEnum::Select,
    'parent_id' => $brandAttribute->id,
    'position' => AttributePositionEnum::EveryWhere
]);

// 3. Marka seçimləri əlavə etmək
$bmwOption = $attributeService->syncAttributeOptions($brandAttribute->id, [
    'translates' => [
        'az' => ['name' => 'BMW'],
        'en' => ['name' => 'BMW']
    ],
    'is_active' => true
]);

// 4. BMW-yə aid model seçimləri əlavə etmək
$attributeService->syncAttributeOptions($modelAttribute->id, [
    'parent_id' => $bmwOption->id, // BMW-yə bağlı
    'translates' => [
        'az' => ['name' => 'X5'],
        'en' => ['name' => 'X5']
    ],
    'is_active' => true
]);
```

### Frontend-də Kateqoriyaları Əldə Etmək
```php
// Ana kateqoriyaları əldə etmək
$categories = $categoryRepository->getActiveParentCategories();

// Alt kateqoriyaları əldə etmək
$children = $categoryRepository->getActiveChildCategories($parentId);

// Kateqoriyanın atributlarını əldə etmək (elan yaratmaq üçün)
$attributes = $categoryRepository->getCategoryWithAttributes($categoryId);
```

## 🌱 Seeding və Test Məlumatları

CategorySeeder və AttributeSeeder, sistem üçün nümunə məlumatlar yaradır:

### Kateqoriyalar
- Uşaq aləmi
- Şəxsi əşyalar
- Ev və bağ üçün
- Elektronika
- Hobbi və asudə
- Nəqliyyat
- Daşınmaz əmlak
- İş elanları
- Heyvanlar
- Xidmətlər və biznes

### Atribut Tipləri
- Əmlak atributları (otaq sayı, sahə, mərtəbə, təmir)
- Nəqliyyat atributları (marka, model, il, mühərrik)
- Elektronika atributları (brend, yaddaş, rəng, vəziyyət)
- Ev və bağ atributları (tip, enerji sinfi, istehsal ölkəsi)
- İş elanları atributları (iş növü, təcrübə, təhsil, əmək haqqı)

## 🚀 Performance Optimizasiyaları

### Cache Mexanizmi
```php
// Cache açar yaratmaq
$cacheKey = "category_attributes_{$id}";

// Nəticələri cache-də saxlamaq
return $this->remember($cacheKey, function () use ($id) {
    // Sorğu...
});

// Cache təmizləmək
$this->clearCache();
```

### Database İndekslər
```php
// Kateqoriyalar cədvəli
$table->index(['is_active', 'order', 'is_default']);

// Atributlar cədvəli
$table->index(['type', 'is_active']);
$table->index('order');

// Atribut seçimləri cədvəli
$table->index(['is_active', 'order']);

// Kateqoriya-atribut əlaqəsi
$table->index(['category_id', 'is_visible', 'order']);
```

### Eager Loading
```php
// Kateqoriyaları əlaqəli məlumatlarla yükləmək
$category = $this->model->query()
    ->with(['attributes' => function ($q) {
        $q->where('is_active', true)
            ->oldest('order')
            ->with(['attribute' => function ($q) {
                $q->with(['options' => function ($q) {
                    $q->where('is_active', true);
                }]);
            }]);
    }])
    ->findOrFail($id);
```

## 🧪 Testing

### Kateqoriya Testi
```php
public function test_can_create_category()
{
    $categoryService = app(CategoryService::class);
    
    $data = [
        'translates' => [
            'az' => [
                'name' => 'Test Kateqoriya',
                'description' => 'Test təsviri'
            ],
            'en' => [
                'name' => 'Test Category',
                'description' => 'Test description'
            ]
        ],
        'parent_id' => 0,
        'is_active' => true
    ];
    
    $category = $categoryService->create($data);
    
    $this->assertDatabaseHas('categories', [
        'id' => $category->id
    ]);
    
    $this->assertEquals('Test Kateqoriya', $category->translate('az')->name);
    $this->assertEquals('Test Category', $category->translate('en')->name);
}
```

### Atribut Testi
```php
public function test_can_create_attribute_and_options()
{
    $attributeService = app(AttributeService::class);
    
    // Atribut yaratmaq
    $attribute = $attributeService->create([
        'translates' => [
            'az' => ['name' => 'Test Atribut'],
            'en' => ['name' => 'Test Attribute']
        ],
        'type' => AttributeTypeEnum::Select,
        'position' => AttributePositionEnum::EveryWhere
    ]);
    
    // Option əlavə etmək
    $attributeService->syncAttributeOptions($attribute->id, [
        'translates' => [
            'az' => ['name' => 'Seçim 1'],
            'en' => ['name' => 'Option 1']
        ],
        'is_active' => true
    ]);
    
    // Yoxlamaq
    $options = $attribute->options;
    $this->assertCount(1, $options);
    $this->assertEquals('Seçim 1', $options[0]->translate('az')->name);
}
```

## 📋 Çoxdilli Dəstək

Sistemdə bütün məlumatlar çoxdilli şəkildə saxlanılır. Nümunə struktur:

```json
{
    "translates": {
        "az": {
            "name": "Avtomobillər",
            "description": "Avtomobil elanları"
        },
        "en": {
            "name": "Cars",
            "description": "Car listings"
        }
    }
}
```

### HasTranslate Trait
```php
public function translate(?string $locale = null): ?object
{
    $locale = $locale ?: app()->getLocale();
    return $this->translates->$locale ?? null;
}
```

## 🧠 Genişləndirmə İmkanları

### Yeni Atribut Tipi Əlavə Etmək
```php
// 1. AttributeTypeEnum-a yeni tip əlavə edin
final class AttributeTypeEnum extends Enum
{
    // Mövcud tiplər...
    
    // Yeni tip
    const Rating = 'rating'; // Reytinq tipli atribut
    
    public static function getDescription($value): string
    {
        return match ($value) {
            // Mövcud tiplər...
            self::Rating => t('enums.attributeType.rating'),
            default => self::getKey($value),
        };
    }
}

// 2. Frontend komponentini və backend işləmə məntiqini əlavə edin
```

### Çoxdilli Dəstəyi Genişləndirmək
```php
// config/app.php
'available_locales' => [
    'az' => 'Azərbaycan',
    'en' => 'English',
    'ru' => 'Русский', // Yeni dil əlavə etmək
],
```

## 🔗 İnteqrasiya İmkanları

### Elan Sistemində İstifadə
```php
// Elan yaratarkən kateqoriya seçimi
$listing->category_id = $request->category_id;

// Seçilmiş kateqoriyaya aid atributları əldə etmək
$attributes = $categoryRepository->getCategoryWithAttributes($listing->category_id);

// Atribut dəyərlərini saxlamaq
$listing->attributes = $request->attributes;
```

### Filter Sistemində İstifadə
```php
// Kateqoriyaya aid filterlər yaratmaq
$filters = $category->attributes()
    ->where('is_visible', true)
    ->with('attribute.options')
    ->get()
    ->map(function ($categoryAttribute) {
        return [
            'id' => $categoryAttribute->attribute_id,
            'name' => $categoryAttribute->attribute->translate()->name,
            'type' => $categoryAttribute->attribute->type,
            'options' => $categoryAttribute->attribute->options
                ->where('is_active', true)
                ->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'name' => $option->translate()->name
                    ];
                })
        ];
    });
```

## 🔒 Təhlükəsizlik

- Bütün kontrollerlar icazə yoxlaması edir (`$this->authorizeAction('read')`)
- Bütün formalar validasiya olunur
- SQL Injection-dan qorunmaq üçün repozitoriya patternindən istifadə olunur
- XSS-dən qorunmaq üçün bütün giriş məlumatları sanitize edilir

## 📚 Təlimat və Yardım

### Kateqoriyalar ilə işləmək
1. Kateqoriya strukturunu hazırlayın
2. Kateqoriyalara atributlar əlavə edin
3. Əlaqəli kateqoriyalar və səsvermə sistemlərini konfiqurasiya edin

### Atributlar ilə işləmək
1. Lazımi atribut tiplərini yaradın
2. Atributlara lazım olduqda seçimlər əlavə edin
3. Asılı atributlar üçün parent-child əlaqəsi qurun

### Frontend İnteqrasiyası
1. Kateqoriya seçimi üçün dropdown və ya ağac strukturu yaradın
2. Seçilmiş kateqoriyaya aid atributları dinamik form şəklində göstərin
3. Seçimlər arasında asılılıq varsa, onları düzgün şəkildə əlaqələndirin

## 🔄 Yenilənmə Qeydləri

**v1.0.0** (2025-01-15)
- İlkin versiya
- Çoxsəviyyəli kateqoriya strukturu
- 25+ atribut tipi dəstəyi
- Çoxdilli dəstək
- Admin panel inteqrasiyası

## 📞 Dəstək

Əlavə suallar və texniki dəstək üçün:
- Email: [support@example.com](mailto:support@example.com)
- Issue tracker: [Github Issues](https://github.com/example/category-system/issues)

## 📄 Lisenziya

Bu layihə MIT lisenziyası altında lisenziyalanıb - ətraflı məlumat üçün [LICENSE](LICENSE) faylına baxın.
