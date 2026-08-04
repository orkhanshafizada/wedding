# Page Management Service - README

## 📋 Ümumi Məlumat

Laravel 12 əsaslı səhifə və widget idarəetmə sistemi. Admin paneldən çoxdilli səhifələr yaratmaq və onlara dinamik widget-lar əlavə etmək üçün hazırlanmışdır.

### Əsas məqsəd:
- Saytın "Haqqımızda", "Məxfilik Siyasəti", "Qaydalar" kimi səhifələrini idarə etmək
- 3 dildə məzmun dəstəyi (AZ, EN, RU)
- Səhifələrə widget əlavə etmək (Icon Box, Banner, Gallery)
- SEO optimizasiyası və cache sistemi

## 🎯 Əsas Xüsusiyyətlər

- ✅ **Çoxdilli səhifələr** - Azərbaycan, İngilis, Rus
- ✅ **Widget sistemi** - Icon Box, Content Block, Banner, Gallery
- ✅ **SEO dəstəyi** - Meta tags, structured data
- ✅ **Şəkil idarəetməsi** - Base64 upload və optimize
- ✅ **Cache sistemi** - Yüksək performans
- ✅ **Auto seeding** - Əsas səhifələr avtomatik yaradılır

## 🏗️ Fayl Strukturu

```
app/
├── Services/Module/PageService.php      # Biznes məntiq
├── Repositories/Module/PageRepository.php # Database əməliyyatları  
├── Models/Module/
│   ├── Page.php                         # Səhifə model
│   └── PageWidget.php                   # Widget model
├── Http/Controllers/Api/
│   ├── Admin/PageController.php         # Admin API
│   └── PageController.php               # Public API
├── Enums/
│   ├── PageTypeEnum.php                 # Səhifə növləri
│   └── WidgetTypeEnum.php               # Widget növləri
└── Rules/
    └── ImageBase64Rule.php              # Şəkil validation
```

## 📊 Database Strukturu

### Pages Table
```sql
- id, uuid, slug                    # Identifikatorlar
- type (terms, policy, about, etc)  # Səhifə növü
- is_active, is_system              # Status sahələri
- translates (JSON)                 # Çoxdilli məzmun
- photo_path                        # Səhifə şəkli
- created_by, updated_by            # Kim əlavə/dəyişib
```

### Page Widgets Table
```sql
- id, uuid, page_id                 # Əsas məlumatlar
- type (icon_box, banner, etc)      # Widget növü
- order                             # Səhifədə sıralama
- is_active                         # Aktiv/deaktiv
- translates (JSON)                 # Widget məzmunu
- photo_path, data (JSON)           # Şəkil və xüsusi məlumatlar
```

### JSON Məzmun Strukturu
```json
{
    "az": {
        "name": "Haqqımızda",
        "content": "<p>Məzmun...</p>",
        "meta_title": "SEO başlıq",
        "meta_description": "SEO təsvir"
    },
    "en": { ... },
    "ru": { ... }
}
```

## 🔗 API Endpoints

### Admin Panel
```http
GET    /api/admin/pages              # Səhifə siyahısı
POST   /api/admin/pages              # Yeni səhifə
GET    /api/admin/pages/{id}         # Səhifə detalları
PUT    /api/admin/pages/{id}         # Səhifə yeniləmə
DELETE /api/admin/pages/{id}         # Səhifə silmə
POST   /api/admin/pages/{id}/action  # Status dəyişmə

POST   /api/admin/pages/widgets           # Widget yaratma
PUT    /api/admin/pages/widgets/{id}      # Widget yeniləmə
DELETE /api/admin/pages/widgets/{id}      # Widget silmə
POST   /api/admin/pages/widgets/reorder   # Widget sıralama
```

### Public API
```http
GET /api/pages              # Aktiv səhifələr
GET /api/pages/{slug}       # Slug ilə səhifə
GET /api/pages/type/{type}  # Növə görə səhifələr
```

## 💻 İstifadə Nümunələri

### Səhifə Yaratma
```bash
curl -X POST "/api/admin/pages" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "type": "standard",
    "is_active": true,
    "translates": {
        "az": {
            "name": "Yeni Səhifə",
            "content": "<p>Məzmun...</p>",
            "meta_title": "SEO başlıq"
        }
    },
    "photo_path": "data:image/jpeg;base64,..."
}'
```

### Widget Yaratma
```json
{
    "page_id": 1,
    "type": "icon_box",
    "order": 1,
    "data": {
        "icon": "star",
        "background": "#ffffff"
    },
    "translates": {
        "az": {
            "title": "Xüsusiyyət",
            "description": "Təsvir...",
            "button_text": "Ətraflı"
        }
    }
}
```

### Widget Sıralama
```json
{
    "orders": [
        {"id": 1, "order": 3},
        {"id": 2, "order": 1},
        {"id": 3, "order": 2}
    ]
}
```

## 🛠 Service Metodları

### PageService
```php
// Səhifə əməliyyatları
findBySlug(string $slug, ?string $locale = null): Page
findByType(string $type): Collection

// Widget əməliyyatları  
createWidget(array $data): PageWidget
updateWidget(int $id, array $data): PageWidget
deleteWidget(int $id): bool
changeWidgetStatus(int $id): PageWidget
updateWidgetsOrder(array $orders): bool
```

### Nümunə istifadə:
```php
// Service injection
$pageService = app(PageService::class);

// Səhifə tapma
$page = $pageService->findBySlug('haqqimizda');

// Widget yaratma
$widget = $pageService->createWidget([
    'page_id' => 1,
    'type' => 'icon_box',
    'translates' => [...]
]);
```

## 🎨 Widget Növləri

### Icon Box
```json
{
    "type": "icon_box",
    "data": {
        "icon": "star",           // İkon adı
        "background": "#f8f9fa",  // Arxa fon
        "text_color": "#333",     // Mətn rəngi
        "icon_color": "#007bff"   // İkon rəngi
    }
}
```

### Banner
```json
{
    "type": "banner", 
    "data": {
        "url": "https://...",     // Link
        "target": "_blank",       // Target
        "overlay": true           // Overlay göstər
    }
}
```

### Content Block
```json
{
    "type": "content_block",
    "data": {
        "layout": "left",         // left, right, center
        "show_button": true,      // Düymə göstər
        "columns": 2              // Sütun sayı
    }
}
```

## 🌱 Seeding

Sistem avtomatik olaraq 3 əsas səhifə yaradır:

```bash
php artisan db:seed --class=PageSeeder
```

**Yaradılan səhifələr:**
- **Məxfilik Siyasəti** (slug: mexfilik-siyaseti)
- **Qaydalar** (slug: qaydalar)
- **Haqqımızda** (slug: haqqimizda) + 3 widget

## 🔧 Validation

### Səhifə yaratma qaydaları:
```php
'translates.az.name' => 'required|string|max:255'  # AZ ad mütləq
'type' => 'required|in:standard,terms,policy,...' # Düzgün növ
'photo_path' => 'nullable|base64_image|max:5MB'   # Şəkil opsional
```

### Widget qaydaları:
```php
'page_id' => 'required|exists:pages,id'           # Mövcud səhifə
'type' => 'required|in:icon_box,banner,...'       # Widget növü
'translates.az.title' => 'required|string'        # AZ başlıq
```

## 🚀 Performance

### Cache Strategiyası
```php
// Cache açarları
"page_slug_{slug}"           # Fərdi səhifə (60 dəq)
"pages_type_{type}"          # Növə görə (30 dəq)  
"page_{id}_widgets"          # Widget-lar (45 dəq)
```

### Database İndekslər
```sql
CREATE INDEX idx_pages_slug ON pages(slug);
CREATE INDEX idx_pages_type ON pages(type);
CREATE INDEX idx_widgets_page_order ON page_widgets(page_id, order);
```

## 🧪 Testing

### Unit Test Nümunəsi
```php
public function test_can_create_page()
{
    $data = [
        'type' => 'standard',
        'translates' => ['az' => ['name' => 'Test']]
    ];
    
    $page = $this->pageService->create($data);
    
    $this->assertEquals('test', $page->slug);
    $this->assertDatabaseHas('pages', ['slug' => 'test']);
}
```

### API Test
```php
public function test_admin_can_create_page()
{
    $admin = User::factory()->admin()->create();
    
    $response = $this->actingAs($admin)
        ->postJson('/api/admin/pages', $pageData);
        
    $response->assertStatus(201);
}
```

## 📋 İcazə Sistemi

**Tələb olunan icazələr:**
- `page_read` - Oxuma
- `page_create` - Yaratma
- `page_update` - Yeniləmə
- `page_delete` - Silmə
- `page_status` - Status dəyişmə

## ⚠️ Məhdudiyyətlər

- Sistem səhifələri silinə bilməz (`is_system = true`)
- Şəkil maksimum 5MB olmalıdır
- Base64 formatında yalnız jpeg, png, webp dəstəklənir
- Widget sıralaması 0-dan başlayır

## 🔄 Yenilənmə Qeydləri

**v1.0.0** (2025-01-15)
- İlk versiya
- Əsas CRUD əməliyyatları
- Widget sistemi
- Çoxdilli dəstək
- Cache implementasiyası
