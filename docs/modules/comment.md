# Comment System Service - README

## 📋 Ümumi Məlumat

Laravel 12 əsaslı hərtərəfli şərh sistemi. İstifadəçilər, elanlar və şirkətlər üçün çoxsəviyyəli şərh sistemi. Reaksiyalar, fayl əlavələri, spam qoruma və emoji dəstəyi daxildir.

### Əsas məqsəd:
- Müxtəlif model türləri üçün şərh sistemi
- Çoxsəviyyəli cavab şərhləri
- Reaksiya və emoji sistemi
- Spam qoruma və moderasiya

## 🎯 Əsas Xüsusiyyətlər

- ✅ **Polymorphic Relations** - İstifadəçi, Elan, Şirkət şərhləri
- ✅ **Nested Comments** - Çoxsəviyyəli cavab sistemi
- ✅ **Reactions System** - Like, Heart, Laugh və s. reaksiyalar
- ✅ **Rich Content** - Emoji, mention, attachment dəstəyi
- ✅ **Edit History** - Şərh redaktə tarixçəsi
- ✅ **Spam Protection** - Avtomatik spam qoruma
- ✅ **Privacy Control** - Şəxsi və ümumi şərhlər
- ✅ **Meta Data System** - Genişləndirilə bilən məlumat sistemi

## 🏗️ Fayl Strukturu

```
app/
├── Services/Module/CommentService.php          # Biznes məntiq
├── Repositories/Module/CommentRepository.php   # Database əməliyyatları
├── Models/Comment.php                          # Comment model və metodlar
├── Http/Controllers/Api/
│   ├── Admin/CommentController.php             # Admin API
│   └── Front/CommentController.php             # Public API
├── Enums/
│   ├── CommentTypeEnum.php                     # Şərh növləri
│   └── CommentReactionType.php                 # Reaksiya növləri
└── Services/Filter/CommentFilter.php           # Filter sistemi

database/
├── migrations/create_comments_table.php        # Database strukturu
└── seeders/CommentSeeder.php                  # Test şərh məlumatları
```

## 📊 Database Strukturu

### Comments Table
```sql
CREATE TABLE comments (
    id BIGINT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE,
    
    -- Polymorphic relation
    commentable_id BIGINT,             # Model ID (User, Listing, Company)
    commentable_type VARCHAR(255),     # Model tipi
    
    -- Şərh məlumatları
    user_id BIGINT,                    # Şərh yazan istifadəçi
    parent_id BIGINT NULL,             # Parent şərh (cavab üçün)
    content TEXT,                      # Şərh mətni
    
    -- Meta məlumatlar (JSON)
    meta_data JSON,                    # Reaksiyalar, fayllar, tarixçə
    
    -- Status
    is_private BOOLEAN DEFAULT FALSE,  # Şəxsi şərh
    status VARCHAR(50) DEFAULT 'active', # active, pending, rejected
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP              # Soft delete
);
```

### Meta Data JSON Strukturu
```json
{
    "reactions": {
        "like": [1, 5, 12],           // User ID-ləri
        "heart": [3, 8],
        "laugh": [7, 15, 23]
    },
    "edit_history": [
        {
            "old_content": "Köhnə mətn",
            "edited_at": "2025-01-15T12:00:00Z",
            "edited_by": 1,
            "reason": "Yazı xətası düzəltmə"
        }
    ],
    "attachments": [
        {
            "type": "image",
            "url": "/storage/comments/abc123/photo.jpg",
            "name": "photo.jpg",
            "size": 1048576
        }
    ],
    "spam_reports": [
        {
            "reported_by": 5,
            "reported_at": "2025-01-15T14:30:00Z",
            "reason": "Uyğunsuz məzmun"
        }
    ]
}
```

## 🔧 Service və Repository Metodları

### CommentService
```php
// BaseCrudService-dən miras alınan metodlar
create(array $data)                    # Yeni şərh yaratmaq
update(int $id, array $data)           # Şərhi yeniləmək
delete(int $id)                        # Şərhi silmək
findById(int $id)                      # ID ilə tapmaq
paginateAndFilter()                    # Filtrlənmiş siyahı
```

### CommentRepository
```php
// Xüsusi metodlar
saveComment(array $data): Comment      # Şərh yaratma/yeniləmə
deleteComment(string $uuid): bool      # UUID ilə silmə
canDeleteComment(Comment $comment): bool # Silmə icazəsi yoxlaması
```

### İstifadə nümunələri:
```php
$commentService = app(CommentService::class);

// Elan üçün şərh yaratmaq
$comment = $commentService->create([
    'commentable_type' => 'App\Models\Listing',
    'commentable_id' => 123,
    'user_id' => auth()->id(),
    'content' => 'Bu məhsul çox yaxşıdır! 👍',
    'is_private' => false
]);

// Şərhə cavab yazmaq
$reply = $commentService->create([
    'commentable_type' => 'App\Models\Listing',
    'commentable_id' => 123,
    'parent_id' => $comment->id,
    'user_id' => auth()->id(),
    'content' => '@username mən də razıyam 😊'
]);
```

## 🔗 API Endpoints

### Public API (Frontend)
```http
POST   /api/comments                   # Şərh yaratma/yeniləmə
DELETE /api/comments/{uuid}            # Şərh silmə
```

### Admin API
```http
GET    /api/admin/comments             # Şərh siyahısı
GET    /api/admin/comments/{id}        # Şərh detalları
PUT    /api/admin/comments/{id}        # Şərh statusu dəyişmə
DELETE /api/admin/comments/{id}        # Şərh silmə
```

## 💻 API İstifadə Nümunələri

### Şərh yaratmaq
```bash
curl -X POST "/api/comments" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "type": "listing",
    "commentable_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "content": "Bu məhsul çox keyfiyyətlidir! Tövsiyə edirəm 👍",
    "is_private": false
}'
```

Response:
```json
{
    "message": "Comment added successfully",
    "comment": {
        "id": 1,
        "content": "Bu məhsul çox keyfiyyətlidir! Tövsiyə edirəm 👍",
        "author": {
            "id": 5,
            "name": "Ayşən Həsənova",
            "avatar": "/storage/avatars/aysen.jpg"
        },
        "created_at": "2025-01-15T12:00:00Z",
        "reactions": {
            "total": 0,
            "types": {},
            "current_user": []
        },
        "replies_count": 0,
        "can_edit": true,
        "is_edited": false,
        "attachments": []
    }
}
```

### Şərhə cavab yazmaq
```bash
curl -X POST "/api/comments" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "type": "listing",
    "commentable_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "parent_id": 1,
    "content": "@aysen_hesenova həqiqətən belə 😊"
}'
```

### Şərhi yeniləmək
```bash
curl -X POST "/api/comments" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "id": 1,
    "content": "Bu məhsul çox keyfiyyətlidir! Mütləq tövsiyə edirəm 👍⭐",
    "edit_reason": "Emoji əlavə etdim"
}'
```

## 📋 Model Metodları və Xüsusiyyətlər

### Comment Model Əsas Metodları
```php
class Comment extends BaseModel
{
    // Polymorphic əlaqə
    public function commentable(): MorphTo
    
    // İstifadəçi əlaqəsi
    public function author(): BelongsTo
    
    // Parent-child əlaqələr
    public function parent(): BelongsTo
    public function replies(): HasMany
    
    // Meta data idarəetməsi
    public function getMetaData(string $key, $default = null)
    public function setMetaData(string $key, $value): bool
    public function removeMetaData(string $key): bool
    
    // Biznes metodları
    public function edit(string $newContent, ?string $reason = null): bool
    public function toggleReaction(string $type): bool
    public function hasReaction(string $type): bool
    public function addAttachment(array $file): bool
    public function markAsSpam(): bool
}
```

### Virtual Attributes
```php
// Formatlanmış məzmun (emoji və mention dəstəyi)
$comment->formatted_content;

// Reaksiyalar xülasəsi
$comment->reactions_summary;
// Qaytarır: ['total' => 5, 'types' => ['like' => 3, 'heart' => 2]]

// Redaktə icazəsi
$comment->can_edit;  // boolean

// Şərh tipi mətni
$comment->commentable_type_text; // "Elan", "Şirkət", "İstifadəçi"
```

### Praktik İstifadə Nümunələri
```php
$comment = Comment::find(1);

// Reaksiya əlavə etmək
$comment->toggleReaction('like');
$comment->toggleReaction('heart');

// Reaksiyanı yoxlamaq
if ($comment->hasReaction('like')) {
    echo "Bu şərhə like vermişsiniz";
}

// Fayl əlavə etmək
$comment->addAttachment([
    'type' => 'image',
    'url' => '/storage/comments/photo.jpg',
    'name' => 'məhsul_fotosu.jpg',
    'size' => 1048576
]);

// Şərhi redaktə etmək
$comment->edit(
    'Yenilənmiş şərh mətni',
    'Əlavə məlumat əlavə etdim'
);

// Spam kimi işarələmək
$comment->markAsSpam();
```

## 🎭 Emoji və Formatting Sistemi

### Emoji Dəstəyi
Sistem avtomatik olaraq sadə simvolları emojilərə çevirir:

```php
// Əsas üz ifadələri
':)' => '😊'    ':-D' => '😃'    ':(' => '😢'
';)' => '😉'    ':P' => '😛'     ':/' => '😕'

// Reaksiyalar
'<3' => '❤️'    ':+1:' => '👍'   ':-1:' => '👎'
':fire:' => '🔥'  ':100:' => '💯'

// Pattern detection
'haha' => '😄'   'lol' => '😂'    'wow' => '😮'
'omg' => '😱'    'wtf' => '😳'
```

### Mention Sistemi
```php
// @username avtomatik linkə çevrilir
"@aysen salam!" => '<a href="/users/aysen">@aysen</a> salam!'
```

## 🌱 Seeding Sistemi

### CommentSeeder
Realistik test məlumatları yaradır:

```bash
php artisan db:seed --class=CommentSeeder
```

**Yaradılan şərh növləri:**
- **İstifadəçi profil şərhləri**: 60-200 şərh
- **Elan şərhləri**: 100-300 şərh (cavablar daxil)
- **Şirkət şərhləri**: 80-250 şərh

**Xüsusiyyətlər:**
- 65% şərhdə emoji istifadəsi
- 20% şərhdə fayl əlavəsi
- 8% spam bildirişi
- Çoxsəviyyəli cavab sistemi
- Realistik Azərbaycan dilində məzmun

### Seeder Nümunə Məzmunları
```php
// Elan şərhləri
"Bu elan diqqətimi cəlb etdi. Qiymət münasibdir 👍"
"Məhsul haqqında əlavə məlumat ala bilərəm? 🤔"
"Nə vaxt baxmaq olar? 📞"

// Şirkət şərhləri  
"Xidmətiniz əla ⭐⭐⭐⭐⭐"
"Müştəri xidmətiniz: Çox yaxşı 👌"
"Tövsiyə edirəm 👍"
```

## 🎯 Reaksiya Sistemi

### Mövcud Reaksiya Növləri
```php
enum CommentReactionType {
    Like = 'like';        // 👍
    Dislike = 'dislike';  // 👎  
    Heart = 'heart';      // ❤️
    Laugh = 'laugh';      // 😂
    Wow = 'wow';         // 😮
    Sad = 'sad';         // 😢
    Angry = 'angry';     // 😠
    Support = 'support'; // 🤝
}
```

### Reaksiya İstifadəsi
```php
// Reaksiya əlavə etmək/çıxarmaq
$comment->toggleReaction('like');

// Bütün reaksiyaları əldə etmək
$reactions = $comment->reactions_summary;
/*
[
    'total' => 8,
    'types' => [
        'like' => 5,
        'heart' => 2,
        'laugh' => 1
    ],
    'current_user' => ['like'] // Cari istifadəçinin reaksiyaları
]
*/

// Konkret reaksiyanı yoxlamaq
if ($comment->hasReaction('heart')) {
    echo "Bu şərhə ürək vermişsiniz ❤️";
}
```

## 🛡️ Spam Qoruma Sistemi

### Avtomatik Spam Aşkarlanması
```php
// 3 və ya daha çox spam bildirişi olan şərhlər avtomatik deaktiv olur
$comment->markAsSpam();

// Spam bildirişləri yoxlamaq
$spamReports = $comment->getMetaData('spam_reports', []);
if (count($spamReports) >= 3) {
    $comment->is_active = false;
    $comment->save();
}
```

### Spam Bildiriş Strukturu
```json
{
    "spam_reports": [
        {
            "reported_by": 5,
            "reported_at": "2025-01-15T14:30:00Z",
            "reason": "Uyğunsuz məzmun"
        },
        {
            "reported_by": 8,
            "reported_at": "2025-01-15T15:00:00Z",
            "reason": "Spam məzmun"
        }
    ]
}
```

## 🔍 Filter Sistemi

### CommentFilter
```php
// Mövcud filterlər
'type' => 'App\Models\Listing'         # Model tipinə görə
'author' => 'user_name'                # Şərh yazanın adı
'status' => 'active|pending|rejected'  # Status filter
'has_reactions' => true                # Reaksiyası olan şərhlər
'has_attachments' => true              # Faylı olan şərhlər
```

### Filter istifadəsi:
```bash
# Elan şərhlərini əldə etmək
GET /api/admin/comments?type=App\Models\Listing

# Konkret istifadəçinin şərhləri
GET /api/admin/comments?author=aysen

# Spam şərhlər
GET /api/admin/comments?status=rejected
```

## 🔧 Validation Rules

### Şərh yaratma/yeniləmə:
```php
'type' => 'required|in:listing,company,user'     # Model tipi
'commentable_uuid' => 'required|uuid|exists'     # Model UUID
'content' => 'required|string|min:3|max:2000'    # Şərh mətni
'parent_id' => 'nullable|exists:comments,id'     # Parent şərh
'is_private' => 'boolean'                        # Gizlilik
'edit_reason' => 'nullable|string|max:255'       # Redaktə səbəbi
```

### Xəta mesajları:
```php
'content.required' => 'Şərh mətni tələb olunur'
'content.min' => 'Şərh minimum 3 simvol olmalıdır'
'content.max' => 'Şərh maksimum 2000 simvol ola bilər'
'commentable_uuid.exists' => 'Göndərilən məlumat yanlışdır'
```

## 🚀 Performance Optimizasiyası

### Database İndekslər
```sql
CREATE INDEX idx_comments_commentable ON comments(commentable_type, commentable_id);
CREATE INDEX idx_comments_user ON comments(user_id);
CREATE INDEX idx_comments_parent ON comments(parent_id);
CREATE INDEX idx_comments_status ON comments(status);
CREATE INDEX idx_comments_active ON comments(is_active);
CREATE INDEX idx_comments_created ON comments(created_at);
```

### Cache Strategiyası
```php
// Şərh sayı cache (30 dəqiqə)
Cache::remember("comments_count_{$modelType}_{$modelId}", 1800, function () {
    return Comment::where('commentable_type', $modelType)
        ->where('commentable_id', $modelId)
        ->active()
        ->count();
});

// İstifadəçinin son şərhləri (15 dəqiqə)
Cache::remember("user_recent_comments_{$userId}", 900, function () use ($userId) {
    return Comment::where('user_id', $userId)
        ->with('commentable')
        ->latest()
        ->take(10)
        ->get();
});
```

## 🧪 Testing

### Unit Test Nümunəsi
```php
public function test_can_create_comment()
{
    $listing = Listing::factory()->create();
    $user = User::factory()->create();
    
    $comment = Comment::create([
        'commentable_type' => Listing::class,
        'commentable_id' => $listing->id,
        'user_id' => $user->id,
        'content' => 'Test şərhi'
    ]);
    
    $this->assertEquals('Test şərhi', $comment->content);
    $this->assertEquals($listing->id, $comment->commentable_id);
}

public function test_comment_reactions()
{
    $comment = Comment::factory()->create();
    
    $this->actingAs($user = User::factory()->create());
    
    // Reaksiya əlavə etmək
    $comment->toggleReaction('like');
    $this->assertTrue($comment->hasReaction('like'));
    
    // Reaksiyanı çıxarmaq
    $comment->toggleReaction('like');
    $this->assertFalse($comment->hasReaction('like'));
}

public function test_spam_protection()
{
    $comment = Comment::factory()->create();
    
    // 3 spam report əlavə etmək
    for ($i = 0; $i < 3; $i++) {
        $this->actingAs(User::factory()->create());
        $comment->markAsSpam();
    }
    
    $this->assertFalse($comment->fresh()->is_active);
}
```

## 📋 İcazə Sistemi

**Tələb olunan icazələr:**
- `comment_read` - Şərh siyahısını görüntüləmə
- `comment_create` - Yeni şərh yaratma
- `comment_update` - Şərhi redaktə etmə
- `comment_delete` - Şərhi silmə
- `comment_moderate` - Şərh moderasiyası

## 🔄 Yenilənmə Qeydləri

**v1.0.0** (2025-01-15)
- İlkin versiya
- Polymorphic comment sistemi
- Reaksiya və emoji dəstəyi
- Spam qoruma sistemi
- Çoxsəviyyəli cavab sistemi
- Meta data genişləndirmə dəstəyi
