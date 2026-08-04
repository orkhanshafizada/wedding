# Complaint Management Service - README

## 📋 Ümumi Məlumat

Laravel 12 əsaslı hərtərəfli şikayət idarəetmə sistemi. İstifadəçilər, şirkətlər və elanlar üçün şikayət sistemi. Mesajlaşma, status izləmə və avtomatik bildiriş sistemi daxildir.

### Əsas məqsəd:
- İstifadəçi, şirkət və elan şikayətləri
- İki tərəfli mesajlaşma sistemi
- Status əsaslı iş prosesi
- Avtomatik bildiriş sistemi

## 🎯 Əsas Xüsusiyyətlər

- ✅ **Polymorphic Complaints** - User, Company, Listing şikayətləri
- ✅ **Message System** - İki tərəfli mesajlaşma
- ✅ **Status Workflow** - Pending → In Progress → Resolved/Rejected
- ✅ **File Attachments** - Base64 fayl yükləmə dəstəyi
- ✅ **Auto Notifications** - Status dəyişikliklərində bildiriş
- ✅ **Admin Panel** - Tam moderasiya paneli
- ✅ **Code Generation** - Unikal şikayət kodları (CPL-12345)
- ✅ **Audit Trail** - Təfsilatılı dəyişiklik izləmə

## 🏗️ Fayl Strukturu

```
app/
├── Services/Module/ComplaintsService.php       # Biznes məntiq
├── Repositories/Module/ComplaintsRepository.php # Database əməliyyatları
├── Models/
│   ├── Complaint.php                           # Şikayət model
│   └── ComplaintMessage.php                    # Mesaj model
├── Http/Controllers/Api/
│   ├── Admin/ComplaintsController.php          # Admin API
│   └── Front/ComplaintsController.php          # User API
├── Enums/
│   ├── ComplaintStatusEnum.php                 # Şikayət statusları
│   ├── ComplaintTypeEnum.php                   # Şikayət növləri
│   └── ComplaintMessageStatusEnum.php          # Mesaj statusları
└── Services/Filter/ComplaintsFilter.php        # Filter sistemi

database/
├── migrations/create_complaints_table.php      # Database strukturu
└── seeders/ComplaintSeeder.php                # Test məlumatları
```

## 📊 Database Strukturu

### Complaints Table
```sql
CREATE TABLE complaints (
    id BIGINT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE,
    code VARCHAR(50) UNIQUE,           # CPL-12345 formatında
    
    -- Şikayətçi
    user_id BIGINT,                    # Şikayət edən istifadəçi
    
    -- Polymorphic relation
    complaintable_id BIGINT,           # Şikayət edilən obyekt ID
    complaintable_type VARCHAR(255),   # User, Company, Listing
    
    -- Şikayət məzmunu
    title VARCHAR(255),                # Şikayət başlığı
    description TEXT,                  # Təfsilatı
    attachments JSON,                  # Əlavə fayllar
    
    -- Status və həll
    status VARCHAR(50) DEFAULT 'pending',
    resolution_note TEXT,              # Admin qeydi
    resolved_at TIMESTAMP,             # Həll tarixi
    resolved_by BIGINT,                # Həll edən admin
    
    -- Audit
    created_by BIGINT,
    updated_by BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

### Complaint Messages Table
```sql
CREATE TABLE complaint_messages (
    id BIGINT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE,
    complaint_id BIGINT,              # Aid olduğu şikayət
    user_id BIGINT,                   # Mesaj yazan
    message TEXT,                     # Mesaj mətni
    attachments JSON,                 # Fayl əlavələri
    status VARCHAR(50) DEFAULT 'pending',
    is_staff_reply BOOLEAN DEFAULT FALSE, # Admin cavabımı?
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

## 🔧 Service Metodları

### ComplaintsService
```php
// CRUD əməliyyatları
create(array $data)                    # Yeni şikayət yaratmaq
findById(int $id)                      # ID ilə tapmaq (with relations)
paginateAndFilter()                    # Filtrlənmiş siyahı

// Xüsusi metodlar
saveComplaint($complaintable, $data)   # Model obyekti ilə şikayət
status(int $id, array $data)           # Status dəyişikliği
reply(int $id, array $data)            # Cavab mesajı
stats(): array                         # Statistika
findByUser(int $userId)                # İstifadəçi şikayətləri
```

### İstifadə nümunələri:
```php
$complaintService = app(ComplaintsService::class);

// İstifadəçi şikayəti yaratmaq
$listing = Listing::find(123);
$complaint = $complaintService->saveComplaint($listing, [
    'title' => 'Məhsul təsvirə uyğun deyil',
    'description' => 'Sifariş etdiyim məhsul...'
]);

// Status dəyişmək
$complaintService->status($complaint->id, [
    'status' => 'resolved',
    'resolution_note' => 'Problem həll edildi'
]);

// Cavab yazmaq
$complaintService->reply($complaint->id, [
    'message' => 'Təşəkkür edirik, yenidən yoxladıq'
]);
```

## 🔗 API Endpoints

### User API (Frontend)
```http
GET    /api/complaints/my              # Mənim şikayətlərim
POST   /api/complaints                 # Yeni şikayət yaratma
GET    /api/complaints/{uuid}          # Şikayət detalları
POST   /api/complaints/{uuid}/reply    # Şikayətə cavab yazma
```

### Admin API
```http
GET    /api/admin/complaints           # Bütün şikayətlər
GET    /api/admin/complaints/{id}      # Şikayət detalları
PUT    /api/admin/complaints/{id}/status # Status dəyişmə
POST   /api/admin/complaints/{id}/reply  # Admin cavabı
GET    /api/admin/complaints/stats     # Statistika
```

## 💻 API İstifadə Nümunələri

### Şikayət yaratmaq
```bash
curl -X POST "/api/complaints" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "complaintable_type": "App\\Models\\Listing",
    "complaintable_id": 123,
    "title": "Məhsul təsvirə uyğun deyil",
    "description": "Sifariş etdiyim iPhone 13 Pro əvəzinə köhnə model göndərildi",
    "attachments": ["data:image/jpeg;base64,..."]
}'
```

Response:
```json
{
    "data": {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "code": "CPL-00001",
        "title": "Məhsul təsvirə uyğun deyil",
        "status": "pending",
        "status_text": "Gözləyir",
        "created_at": "2025-01-15T12:00:00Z",
        "user": {
            "id": 5,
            "name": "Ali Məmmədov"
        },
        "complaintable": {
            "id": 123,
            "title": "iPhone 13 Pro 256GB"
        },
        "messages_count": 0
    }
}
```

### Admin tərəfindən status dəyişmək
```bash
curl -X PUT "/api/admin/complaints/1/status" \
-H "Authorization: Bearer ADMIN_TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "status": "resolved",
    "resolution_note": "Satıcı ilə əlaqə saxlanıldı, məhsul dəyişdirildi"
}'
```

### Şikayət statistikası
```bash
curl -X GET "/api/admin/complaints/stats" \
-H "Authorization: Bearer ADMIN_TOKEN"
```

Response:
```json
{
    "total": 250,
    "pending": 45,
    "in_progress": 30,
    "resolved": 160,
    "rejected": 10,
    "closed": 5
}
```

## 📋 Model Metodları və Enum-lar

### Complaint Status Workflow
```php
enum ComplaintStatusEnum {
    Pending = 'pending';        // Yeni şikayət
    InProgress = 'processing';  // İncelənir
    Resolved = 'resolved';      // Həll edilib
    Rejected = 'rejected';      # Rədd edilib
    Closed = 'closed';          # Bağlanıb
}
```

### Message Status
```php
enum ComplaintMessageStatusEnum {
    Pending = 'pending';        // Yeni mesaj
    Read = 'read';             // Oxunub
    Responded = 'responded';    // Cavab verilib
    Hidden = 'hidden';         // Gizlədilib
}
```

### Complaint Model Metodları
```php
class Complaint extends BaseModel
{
    // Scope-lar
    public function scopePending($query)
    public function scopeInProgress($query)
    public function scopeResolved($query)
    
    // Helper metodlar
    public function canReply(): bool           # Cavab yazıla bilərmi?
    public function isResolved(): bool
    public function isPending(): bool
    public function getNotifiableUser(): ?User # Bildiriş göndəriləcək user
    
    // Relationships
    public function user(): BelongsTo          # Şikayətçi
    public function complaintable(): MorphTo   # Şikayət edilən obyekt
    public function messages(): HasMany       # Mesajlar
    public function resolver(): BelongsTo     # Həll edən admin
}
```

### Praktik İstifadə
```php
$complaint = Complaint::find(1);

// Status yoxlaması
if ($complaint->canReply()) {
    $complaint->messages()->create([
        'user_id' => auth()->id(),
        'message' => 'Əlavə məlumat...',
        'is_staff_reply' => auth()->user()->hasRole('admin')
    ]);
}

// Bildiriş göndərmək
$notifiableUser = $complaint->getNotifiableUser();
if ($notifiableUser) {
    $notifiableUser->notify(/* ... */);
}

// Status filtrləri
$pendingComplaints = Complaint::pending()->get();
$myComplaints = Complaint::where('user_id', auth()->id())->get();
```

## 🌱 Seeding Sistemi

### ComplaintSeeder
500 şikayət və 1500+ mesaj yaradır:

```bash
php artisan db:seed --class=ComplaintSeeder
```

**Yaradılan məlumatlar:**
- **500 şikayət**: User, Company, Listing üçün
- **1500+ mesaj**: Hər şikayətə 1-5 mesaj
- **Status bölgüsü**: 40% pending, 30% in_progress, 15% resolved
- **Realistic məzmun**: Azərbaycanca şikayət başlıqları və təsvirləri

**Nümunə şikayət başlıqları:**
```php
'Xidmət keyfiyyəti aşağıdır'
'Məhsul vaxtında çatdırılmadı'
'Yanlış məlumat verildi'
'Pulumu geri qaytarmadılar'
'Elan saxtadır'
```

**Nümunə mesajlar:**
```php
// İstifadəçi mesajları
'Nə vaxta qədər yoxlanılacaq? Artıq 1 həftədir gözləyirəm!'
'Əlavə məlumat vermək istəyirəm, necə edim?'

// Admin cavabları
'Şikayətiniz yoxlanılır, tezliklə cavab veriləcək.'
'Problem həll olundu, əlavə sualınız varsa yazın.'
```

## 🔍 Filter Sistemi

### ComplaintsFilter
```php
// Mövcud filterlər
'search' => 'search_term'              # Kod əsasında axtarış
'status' => 'pending|processing|resolved' # Status filter
'user' => 'user_name'                  # Şikayətçi adı
'type' => 'App\Models\Listing'         # Şikayət obyekt tipi
'date_range' => ['from' => '...', 'to' => '...'] # Tarix aralığı
```

### Filter istifadəsi:
```bash
# Pending şikayətlər
GET /api/admin/complaints?status=pending

# Müəyyən istifadəçinin şikayətləri
GET /api/admin/complaints?user=ali_memmedov

# Elan şikayətləri
GET /api/admin/complaints?type=App\Models\Listing

# Kod ilə axtarış
GET /api/admin/complaints?search=CPL-00123
```

## 🔔 Bildiriş Sistemi

### Avtomatik Bildirişlər
Status dəyişikliklərində avtomatik bildiriş göndərilir:

```php
// Status dəyişdikdə
$notificationTypes = [
    'processing' => NotificationTypeEnum::COMPLAINT_PROGRESS,
    'resolved' => NotificationTypeEnum::COMPLAINT_RESOLVED,
    'rejected' => NotificationTypeEnum::COMPLAINT_REJECT,
    'closed' => NotificationTypeEnum::COMPLAINT_CLOSED,
];

// Mesaj cavabında
NotificationTypeEnum::COMPLAINT_REPLY
```

### Bildiriş məzmunu:
```php
$complaint->user->notify(
    type: NotificationTypeEnum::COMPLAINT_RESOLVED,
    data: [
        'title' => $complaint->title,
        'resolution_note' => $complaint->resolution_note
    ]
);
```

## 🚀 Performance Optimizasiyası

### Database İndekslər
```sql
CREATE INDEX idx_complaints_user ON complaints(user_id);
CREATE INDEX idx_complaints_status ON complaints(status);
CREATE INDEX idx_complaints_type ON complaints(complaintable_type, complaintable_id);
CREATE INDEX idx_complaints_code ON complaints(code);
CREATE INDEX idx_complaints_created ON complaints(created_at);

CREATE INDEX idx_messages_complaint ON complaint_messages(complaint_id, created_at);
CREATE INDEX idx_messages_status ON complaint_messages(status, created_at);
```

### Cache Strategiyası
```php
// İstifadəçi şikayətləri cache (30 dəqiqə)
Cache::remember("user_complaints_{$userId}_{$status}", 1800, function () {
    return $this->findByUser($userId, $status);
});

// Statistika cache (1 saat)
Cache::remember('complaint_stats', 3600, function () {
    return $this->getStatistics();
});

// Mesaj sayı cache (15 dəqiqə)
Cache::remember("complaint_messages_count_{$complaintId}", 900, function () {
    return ComplaintMessage::where('complaint_id', $complaintId)->count();
});
```

## 🧪 Testing

### Unit Test Nümunəsi
```php
public function test_can_create_complaint()
{
    $user = User::factory()->create();
    $listing = Listing::factory()->create();
    
    $complaint = Complaint::create([
        'user_id' => $user->id,
        'complaintable_type' => Listing::class,
        'complaintable_id' => $listing->id,
        'title' => 'Test şikayəti',
        'description' => 'Test təsviri'
    ]);
    
    $this->assertEquals('pending', $complaint->status);
    $this->assertTrue($complaint->canReply());
    $this->assertStringStartsWith('CPL-', $complaint->code);
}

public function test_complaint_workflow()
{
    $complaint = Complaint::factory()->create();
    
    // Status dəyişikliyi
    $complaint->update(['status' => 'processing']);
    $this->assertTrue($complaint->isInProgress());
    
    // Mesaj əlavə etmək
    $message = $complaint->messages()->create([
        'user_id' => $complaint->user_id,
        'message' => 'Əlavə məlumat',
        'is_staff_reply' => false
    ]);
    
    $this->assertEquals(1, $complaint->messages()->count());
}

public function test_cannot_reply_to_closed_complaint()
{
    $complaint = Complaint::factory()->create([
        'status' => 'closed'
    ]);
    
    $this->assertFalse($complaint->canReply());
}
```

### API Test
```php
public function test_user_can_create_complaint()
{
    $user = User::factory()->create();
    $listing = Listing::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/complaints', [
            'complaintable_type' => Listing::class,
            'complaintable_id' => $listing->id,
            'title' => 'Test şikayəti',
            'description' => 'Test təsviri'
        ]);
        
    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'code', 'title', 'status']
        ]);
}

public function test_admin_can_change_status()
{
    $admin = User::factory()->admin()->create();
    $complaint = Complaint::factory()->create();
    
    $response = $this->actingAs($admin)
        ->putJson("/api/admin/complaints/{$complaint->id}/status", [
            'status' => 'resolved',
            'resolution_note' => 'Həll edildi'
        ]);
        
    $response->assertOk();
    $this->assertEquals('resolved', $complaint->fresh()->status);
}
```

## 🔧 Validation Rules

### Şikayət yaratma:
```php
'complaintable_type' => 'required|string|in:App\Models\User,App\Models\Company,App\Models\Listing'
'complaintable_id' => 'required|integer|exists:...'
'title' => 'required|string|max:255'
'description' => 'required|string|min:10'
'attachments' => 'nullable|array|max:5'
'attachments.*' => [new ImageBase64Rule, new Base64ImageControlRule]
```

### Status dəyişikliyi:
```php
'status' => 'required|in:pending,processing,resolved,rejected,closed'
'resolution_note' => 'required_if:status,resolved|string|max:1000'
```

### Mesaj yaratma:
```php
'message' => 'required|string|min:5|max:2000'
'attachments' => 'nullable|array|max:3'
'attachments.*' => [new ImageBase64Rule, new Base64ImageControlRule]
```

## 📋 İcazə Sistemi

**Tələb olunan icazələr:**
- `complaint_read` - Şikayət siyahısını görüntüləmə
- `complaint_create` - Yeni şikayət yaratma
- `complaint_status` - Status dəyişdirmə
- `complaint_reply` - Admin cavabı yazma
- `complaint_delete` - Şikayət silmə

## 🔄 İş Prosesi (Workflow)

### Tipik Şikayət Dövriyyəsi:
1. **İstifadəçi şikayət yaradır** → Status: `pending`
2. **Admin şikayəti görür** → Status: `processing`
3. **Araşdırma aparılır** → Mesaj mübadiləsi
4. **Həll edilir** → Status: `resolved` + resolution_note
5. **Və ya rədd edilir** → Status: `rejected` + səbəb

### Avtomatik proseslər:
- Şikayət yaradıldıqda admin-lərə bildiriş
- Status dəyişdikdə istifadəçiyə bildiriş
- Mesaj yazıldıqda qarşı tərəfə bildiriş
- Code avtomatik generasiyası (CPL-XXXXX)

## 🔄 Yenilənmə Qeydləri

**v1.0.0** (2025-01-15)
- İlkin versiya
- Polymorphic complaint sistemi
- İki tərəfli mesajlaşma
- Status workflow sistemi
- Avtomatik bildiriş sistemi
- Admin moderasiya paneli
