# SEO Link Management Service - README

## 📋 Ümumi Məlumat

Laravel 12 əsaslı hərtərəfli SEO idarəetmə sistemi. Saytın hər bir səhifəsi üçün meta tag-lar, OpenGraph, Twitter Card və texniki SEO məlumatlarını idarə edir. SEO skoru hesablama və avtomatik təkliflər də daxildir.

### Əsas məqsəd:
- Hər URL üçün tam SEO məlumat idarəetməsi
- Meta tag-ların avtomatik generasiyası və optimizasiyası
- SEO skor hesablama və təhlil sistemi
- Sitemap.xml və robots.txt avtomatik yaratma

## 🎯 Əsas Xüsusiyyətlər

- ✅ **Tam Meta Tag İdarəsi** - Title, Description, Keywords, Robots
- ✅ **Social Media Optimization** - OpenGraph və Twitter Card
- ✅ **SEO Score Calculator** - 100 ballıq qiymətləndirmə sistemi
- ✅ **Auto Suggestions** - Meta tag təklifləri generasiyası
- ✅ **Morphable Relations** - Hər növ model ilə əlaqə
- ✅ **Sitemap Generation** - XML sitemap avtomatik yaratma
- ✅ **Preview System** - Google və sosial media preview
- ✅ **History Tracking** - Dəyişiklik tarixçəsi

## 🏗️ Fayl Strukturu

```
app/
├── Services/Module/
│   ├── SeoLinkService.php                  # Əsas SEO biznes məntiq
│   └── SeoLinkToolsService.php             # Sitemap və robots.txt
├── Repositories/Module/SeoLinkRepository.php # Database əməliyyatları
├── Models/SeoLink.php                       # SEO model və metodları
├── Http/Controllers/Api/Admin/
│   ├── SeoLinkController.php               # SEO CRUD API
│   └── SeoLinkToolsController.php          # Tools API
├── Services/Helpers/Seo/
│   ├── SeoScoreCalculator.php              # SEO skor hesablama
│   └── MetaSuggestionGenerator.php         # Təklif generasiyası
└── Services/Filter/SeoLinkFilter.php       # Filter sistemi

database/
├── migrations/create_seo_links_table.php   # Database strukturu
└── seeders/SeoLinkSeeder.php              # Test SEO məlumatları
```

## 📊 Database Strukturu

### SEO Links Table
```sql
CREATE TABLE seo_links (
    id BIGINT PRIMARY KEY,
    uuid VARCHAR(36),
    code VARCHAR(50) UNIQUE,
    url VARCHAR(255) UNIQUE,            # SEO-nun aid olduğu URL
    
    -- Morphable relation
    seoable_type VARCHAR(255),          # Model tipi (Category, Page)
    seoable_id BIGINT,                  # Model ID
    
    -- Meta tag grupları (JSON)
    basic_meta JSON,                    # title, description, keywords
    open_graph JSON,                    # og:title, og:description, og:image
    twitter JSON,                       # twitter:card, twitter:title
    technical JSON,                     # canonical, robots, language
    custom_tags JSON,                   # Xüsusi meta tag-lar
    
    -- SEO analiz məlumatları
    score INTEGER DEFAULT 0,           # SEO skoru (0-100)
    analysis JSON,                     # Analiz detalları
    history JSON,                      # Dəyişiklik tarixçəsi
    
    -- Sitemap parametrləri
    is_sitemap BOOLEAN DEFAULT TRUE,   # Sitemap-də göstərilsin
    sitemap_priority VARCHAR(10) DEFAULT '0.5',
    sitemap_frequency VARCHAR(20) DEFAULT 'weekly',
    
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT,
    updated_by BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

### JSON Strukturu Nümunələri

#### Basic Meta JSON:
```json
{
    "title": "Əsas Səhifə | Sayt Adı",
    "description": "Saytın əsas səhifəsi haqqında 160 simvollik təsvir",
    "keywords": "açar söz 1, açar söz 2, açar söz 3",
    "robots": {
        "index": true,
        "follow": true
    },
    "viewport": "width=device-width, initial-scale=1.0"
}
```

#### OpenGraph JSON:
```json
{
    "og:title": "Sosial media üçün başlıq",
    "og:description": "Sosial media üçün təsvir",
    "og:image": "https://example.com/image.jpg",
    "og:type": "website",
    "og:url": "https://example.com/page",
    "og:site_name": "Sayt Adı"
}
```

## 🔧 Service Metodları

### SeoLinkService
```php
// CRUD əməliyyatları
create(array $data): SeoLink              # Yeni SEO yaratmaq
getByUrl(string $url): ?SeoLink           # URL-ə görə tapmaq

// Meta tag idarəetməsi
addMetaTag(string $uuid, array $tagData): SeoLink
removeMetaTag(string $uuid, string $group, string $type): SeoLink

// Analiz və təkliflər
analyzeSeo(string $uuid): array           # SEO analizi
generateSuggestions(string $uuid): Collection # Təkliflər
generatePreviews(string $uuid): array     # Preview məlumatları

// Toplu əməliyyatlar
bulkUpdate(array $items): Collection      # Toplu yeniləmə
getSitemapData(): Collection              # Sitemap üçün data
```

### SeoLinkToolsService
```php
// SEO faylları generasiyası
generateSitemap(): string                 # sitemap.xml yaratmaq
generateRobots(): string                  # robots.txt yaratmaq
generateSitemapIndex(): string            # sitemap index
clearCache(): void                        # Cache təmizləmək
```

### İstifadə nümunələri:
```php
$seoService = app(SeoLinkService::class);

// Yeni SEO yaratmaq
$seo = $seoService->create([
    'url' => '/products/iphone-13',
    'basic_meta' => [
        'title' => 'iPhone 13 Pro | Ən yaxşı qiymətə',
        'description' => 'iPhone 13 Pro 256GB Space Gray...'
    ],
    'seoable_type' => 'App\Models\Product',
    'seoable_id' => 123
]);

// Meta tag əlavə etmək
$seoService->addMetaTag($seo->uuid, [
    'group' => 'open_graph',
    'type' => 'og:price:amount',
    'content' => '1999.99'
]);

// SEO analizi
$analysis = $seoService->analyzeSeo($seo->uuid);
echo "SEO Skoru: " . $analysis['overview']['total_score'];
```

## 🔗 API Endpoints

### Admin Panel SEO İdarəsi
```http
GET    /api/admin/seo-links              # SEO siyahısı
POST   /api/admin/seo-links              # Yeni SEO yaratma
GET    /api/admin/seo-links/{id}         # SEO detalları
PUT    /api/admin/seo-links/{id}         # SEO yeniləmə
DELETE /api/admin/seo-links/{id}         # SEO silmə

# Xüsusi əməliyyatlar
POST   /api/admin/seo-links/{uuid}/tags       # Meta tag əlavə etmə
DELETE /api/admin/seo-links/{uuid}/tags       # Meta tag silmə
GET    /api/admin/seo-links/{uuid}/analyze    # SEO analizi
GET    /api/admin/seo-links/{uuid}/preview    # Preview generasiya
GET    /api/admin/seo-links/{uuid}/suggestions # Təkliflər
GET    /api/admin/seo-links/{uuid}/history    # Tarixçə
```

### SEO Tools API
```http
GET    /sitemap.xml                     # XML sitemap
GET    /robots.txt                      # Robots faylı
GET    /sitemap-index.xml              # Sitemap index
```

### Public API
```http
GET    /api/seo?url=/page-url          # URL üçün SEO məlumatları
```

## 💻 API İstifadə Nümunələri

### SEO yaratmaq
```bash
curl -X POST "/api/admin/seo-links" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "url": "/category/elektronika",
    "seoable_type": "App\\Models\\Category",
    "seoable_id": 5,
    "basic_meta": {
        "title": "Elektronika Kateqoriyası | Sayt",
        "description": "Ən yaxşı elektronika məhsulları...",
        "keywords": "elektronika, telefon, laptop"
    },
    "open_graph": {
        "og:title": "Elektronika Məhsulları",
        "og:description": "Ən yaxşı elektronika məhsulları",
        "og:image": "https://example.com/og-electronics.jpg"
    },
    "sitemap_priority": "0.8",
    "sitemap_frequency": "daily"
}'
```

### Meta tag əlavə etmək
```bash
curl -X POST "/api/admin/seo-links/{uuid}/tags" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "group": "technical",
    "type": "author",
    "content": "Admin"
}'
```

### SEO analizi əldə etmək
```bash
curl -X GET "/api/admin/seo-links/{uuid}/analyze" \
-H "Authorization: Bearer TOKEN"
```

## 🎯 SEO Score Calculator

### Qiymətləndirmə Komponentləri
```php
// Maksimum ballar
const MAX_SCORES = [
    'basic_meta' => [
        'title' => 20,       // Title düzgünlüyü
        'description' => 20, // Description düzgünlüyü  
        'keywords' => 10     // Keywords optimizasiyası
    ],
    'social_media' => [
        'open_graph' => 15,  // OpenGraph tamlığı
        'twitter' => 15      // Twitter Card tamlığı
    ],
    'technical' => [
        'canonical' => 10,   // Canonical URL
        'robots' => 5,       // Robots direktivi
        'language' => 5      // Dil təyin edilməsi
    ]
];
```

### Skor Hesablama Meyarları:

#### Title Analizi (20 bal):
- **50-60 simvol**: 20 bal (ideal)
- **40-70 simvol**: 15 bal (yaxşı)
- **30-80 simvol**: 10 bal (minimal)
- Brend adının olması: +5 bal

#### Description Analizi (20 bal):
- **150-160 simvol**: 20 bal (ideal)
- **140-170 simvol**: 15 bal (yaxşı)
- **120-180 simvol**: 10 bal (minimal)
- Keywords istifadəsi: hər keyword üçün +2 bal

#### Sosial Media (30 bal):
- OpenGraph tam tag-ları: 15 bal
- Twitter Card tam tag-ları: 15 bal

### Skor Statusları:
- **90-100**: Əla SEO
- **70-89**: Yaxşı SEO
- **50-69**: Orta SEO
- **0-49**: Zəif SEO

## 🤖 Meta Suggestion Generator

### Avtomatik Təkliflər:

#### Title əsasında:
```php
// OpenGraph title generasiyası
if (empty($seo->open_graph['og:title'])) {
    $ogTitle = $this->generateSocialTitle($title);
    // Təklif: "OpenGraph title avtomatik generasiya edildi"
}

// Title uzunluğu yoxlaması
if ($titleLength < 30 || $titleLength > 60) {
    // Təklif: "Title uzunluğu optimal deyil"
}
```

#### Description əsasında:
```php
// Sosial media description-ı
$socialDesc = Str::limit($description, 200, '...');

// Title-description əlaqəsi yoxlaması
if (!$this->titleDescriptionRelation($title, $description)) {
    // Təklif: "Description-da title-dan açar söz istifadə edin"
}
```

## 🗺️ Sitemap & Tools

### Sitemap.xml Generasiyası:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://example.com/</loc>
        <lastmod>2025-01-15T12:00:00+00:00</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://example.com/category/elektronika</loc>
        <lastmod>2025-01-15T10:30:00+00:00</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
</urlset>
```

### Robots.txt Generasiyası:
```txt
User-agent: *
Disallow: /admin/
Disallow: /api/
Disallow: /login
Allow: /storage/images/
Allow: /storage/products/

Host: https://example.com
Sitemap: https://example.com/sitemap.xml
```

## 🔄 Model Metodları

### Meta Tag Əməliyyatları:
```php
$seo = SeoLink::find(1);

// Meta tag əlavə etmək
$seo->addMetaTag('open_graph', 'og:price:amount', '1999.99');

// Meta tag silmək
$seo->removeMetaTag('twitter', 'twitter:site');

// Meta tag yeniləmək
$seo->updateMetaTag('basic_meta', 'title', 'Yeni başlıq');
```

### Preview Generasiyası:
```php
// HTML meta tag-ları
$html = $seo->generateMetaTags();

// Google SERP preview
$googlePreview = $seo->getGooglePreviewData();
// Qaytarır: ['title' => '...', 'description' => '...', 'url' => '...']

// Sosial media preview
$socialPreview = $seo->getSocialPreviewData();
// Qaytarır: ['og' => [...], 'twitter' => [...]]
```

### Scope-lar:
```php
// Aktiv SEO yazıları
SeoLink::active()->get();

// Sitemap üçün uyğun olanlar
SeoLink::forSitemap()->get();

// URL-ə görə axtarış
SeoLink::byUrl('/category/elektronika')->first();
```

## 🌱 Seeding Sistemi

### SeoLinkSeeder
Test məlumatları ilə müxtəlif səhifə növləri üçün SEO yaradır:

```bash
php artisan db:seed --class=SeoLinkSeeder
```

**Yaradılan SEO növləri:**
- **Ana səhifə**: Priority 1.0, frequency "always"
- **Kateqoriyalar**: Priority 0.8, frequency "daily"
- **Məhsullar**: Priority 0.6, frequency "daily"
- **Blog yazıları**: Priority 0.7, frequency "weekly"
- **Statik səhifələr**: Priority 0.5, frequency "monthly"

**Avtomatik SEO skor hesablama** hər yaradılan entry üçün aparılır.

## 🚀 Performance Optimizasiyası

### Cache Strategiyası:
```php
// URL cache (1 saat)
'seo_url_/category/elektronika' => SeoLink data

// Sitemap cache (6 saat) 
'sitemap_content' => XML string
'robots_content' => Text string

// Type cache
'seo_by_type_App\Models\Category' => Collection
```

### Database İndekslər:
```sql
CREATE INDEX idx_seo_url ON seo_links(url);
CREATE INDEX idx_seo_active ON seo_links(is_active);
CREATE INDEX idx_seo_sitemap ON seo_links(is_sitemap);
CREATE INDEX idx_seo_type ON seo_links(seoable_type, seoable_id);
CREATE INDEX idx_seo_score ON seo_links(score);
```

## 🧪 Testing

### Unit Test Nümunəsi:
```php
public function test_can_create_seo_with_meta_tags()
{
    $data = [
        'url' => '/test-page',
        'basic_meta' => [
            'title' => 'Test Səhifə',
            'description' => 'Test təsviri'
        ]
    ];
    
    $seo = $this->seoService->create($data);
    
    $this->assertEquals('/test-page', $seo->url);
    $this->assertGreaterThan(0, $seo->score);
}

public function test_calculates_seo_score_correctly()
{
    $seo = SeoLink::factory()->create([
        'basic_meta' => [
            'title' => str_repeat('a', 55), // Optimal uzunluq
            'description' => str_repeat('b', 155) // Optimal uzunluq
        ]
    ]);
    
    $this->assertGreaterThanOrEqual(40, $seo->score);
}
```

## 🔧 Validation Rules

### SEO yaratma/yeniləmə:
```php
'url' => 'required|string|unique:seo_links,url'
'basic_meta.title' => 'required|string|max:60'
'basic_meta.description' => 'required|string|max:160'
'open_graph.og:title' => 'nullable|string|max:95'
'twitter.twitter:title' => 'nullable|string|max:70'
'sitemap_priority' => 'nullable|numeric|min:0.1|max:1.0'
'sitemap_frequency' => 'nullable|in:always,hourly,daily,weekly,monthly,yearly'
```

## 📋 İcazə Sistemi

**Tələb olunan icazələr:**
- `seo_read` - SEO məlumatlarını oxuma
- `seo_create` - Yeni SEO yaratma
- `seo_update` - SEO məlumatlarını yeniləmə
- `seo_delete` - SEO məlumatlarını silmə

## 🔄 Yenilənmə Qeydləri

**v1.0.0** (2025-01-15)
- İlkin versiya
- Tam meta tag idarəetmə sistemi
- SEO skor hesablama
- Avtomatik sitemap generasiyası
- Təklif və analiz sistemi
