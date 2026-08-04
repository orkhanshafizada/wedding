# Messaging Service

<img src="https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12.0"/>
<img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2"/>
<img src="https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge" alt="MIT License"/>

## 📋 Ümumi Məlumat

Laravel 12 əsaslı hərtərəfli mesajlaşma sistemi. İstifadəçilər arasında real-time mesajlaşma, fayl paylaşımı, mesaj statusları və admin idarəetmə funksiyaları təqdim edir.

### Əsas məqsəd:
- İstifadəçilər arası mesajlaşma sistemi
- Müxtəlif növ (mətn, şəkil, fayl, səs) mesajlar
- Status izləmə (göndərildi, çatdırıldı, oxundu)
- İstifadəçi bloklaması və mesajlaşma məhdudlaşdırması
- Admin monitorinq və moderasiya paneli

## 🎯 Əsas Xüsusiyyətlər

- ✅ **Real-time Mesajlaşma** - İstifadəçilər arası ani mesajlaşma
- ✅ **Müxtəlif Mesaj Növləri** - Mətn, şəkil, fayl və səs mesajları
- ✅ **Mesaj Statusları** - Göndərildi, çatdırıldı, oxundu
- ✅ **Söhbət İdarəetməsi** - Söhbətləri pinləmək, arxivləşdirmək
- ✅ **İstifadəçi Bloklaması** - İstənməyən mesajlaşmaların qarşısını almaq
- ✅ **Fayl İdarəetməsi** - Şəkil, sənəd və səs faylları dəstəyi
- ✅ **Admin Paneli** - Söhbətləri izləmək və müdaxilə etmək imkanı
- ✅ **Statistika Dashboard** - Mesajlaşma və aktivlik statistikaları
- ✅ **Sistem Mesajları** - Avtomatik bildiriş və status mesajları
- ✅ **Toplu Mesaj Göndərmə** - Admin tərəfindən çoxlu istifadəçilərə eyni mesajı göndərmək

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
php artisan db:seed --class=MessagingSystemSeeder

# Xidməti başlatmaq
php artisan serve
```

## 🏗️ Fayl Strukturu

```
app/
├── Services/Module/MessagingService.php       # Əsas mesajlaşma biznez məntiq
├── Repositories/Module/MessagingRepository.php # Database əməliyyatları
├── Models/
│   ├── Conversation.php                       # Söhbət modeli
│   ├── Message.php                            # Mesaj modeli
│   └── UserBlock.php                          # İstifadəçi blok modeli
├── Http/Controllers/Api/
│   ├── Admin/MessagingController.php          # Admin API
│   └── Front/MessagingController.php          # İstifadəçi API
├── Enums/
│   ├── ConversationStatusEnum.php             # Söhbət statusları
│   ├── ConversationTypeEnum.php               # Söhbət növləri
│   ├── MessageStatusEnum.php                  # Mesaj statusları
│   └── MessageTypeEnum.php                    # Mesaj növləri
└── Services/Filter/MessagingFilter.php        # Filter sistemi

database/
├── migrations/create_messages_table.php       # Database strukturu
└── seeders/MessagingSystemSeeder.php          # Test məlumatları
```

## 📊 Database Strukturu

### Conversations Table Migration
```php
Schema::create('conversations', function (Blueprint $table) {
    $table->id();
    $table->key(); // UUID yaradır

    // Söhbətin növü və statusu
    $table->enum('type', ConversationTypeEnum::getValues())
        ->default(ConversationTypeEnum::LISTING);
    $table->enum('status', ConversationStatusEnum::getValues())
        ->default(ConversationStatusEnum::ACTIVE);

    // Əlaqələr
    $table->nullableMorphs('conversationable');
    $table->foreignId('creator_id')->constrained('users');
    $table->foreignId('receiver_id')->constrained('users');

    // Meta məlumatlar
    $table->json('meta_data')->nullable();

    // Aktivlik göstəriciləri
    $table->timestamp('last_activity_at')->nullable();
    $table->boolean('is_pinned')->default(false);
    $table->timestamp('archived_at')->nullable();

    // Sistem sütunları
    $table->timestamps();
    $table->softDeletes();

    // İndekslər
    $table->index(['creator_id', 'status', 'last_activity_at']);
    $table->index(['receiver_id', 'status', 'last_activity_at']);
    $table->index(['conversationable_id', 'type']);
    $table->index(['type', 'status']);
    $table->index('is_pinned');
});
```

### Messages Table Migration
```php
Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->key(); // UUID formatında açar

    // Mesajın aid olduğu söhbət və göndərən
    $table->foreignId('conversation_id')
        ->constrained()
        ->onDelete('cascade');
    $table->foreignId('sender_id')
        ->constrained('users');

    // Mesaj məzmunu və tipi
    $table->enum('type', MessageTypeEnum::getValues())
        ->default(MessageTypeEnum::TEXT);
    $table->text('content')->nullable();
    $table->json('attachments')->nullable();
    $table->json('meta_data')->nullable();

    // Mesajın vəziyyəti
    $table->enum('status', MessageStatusEnum::getValues())
        ->default(MessageStatusEnum::SENT);
    $table->boolean('is_system')->default(false);
    $table->boolean('is_edited')->default(false);
    $table->timestamp('edited_at')->nullable();
    $table->timestamp('delivered_at')->nullable();
    $table->timestamp('read_at')->nullable();

    // Sistem sütunları
    $table->timestamps();
    $table->softDeletes();

    // İndekslər
    $table->index(['conversation_id', 'created_at']);
    $table->index(['sender_id', 'created_at']);
    $table->index(['status', 'created_at']);
});
```

### User Blocks Table Migration
```php
Schema::create('user_blocks', function (Blueprint $table) {
    $table->id();
    $table->key(); // UUID yaradır

    // İstifadəçi əlaqələri
    $table->foreignId('blocker_id')
        ->comment('Blok edən istifadəçi')
        ->constrained('users')
        ->onDelete('cascade');

    $table->foreignId('blocked_id')
        ->comment('Blok edilən istifadəçi')
        ->constrained('users')
        ->onDelete('cascade');

    // Əlavə məlumatlar
    $table->text('reason')->nullable()->comment('Bloklama səbəbi');
    $table->json('meta_data')->nullable()->comment('Əlavə məlumatlar');

    // Eyni istifadəçini təkrar bloklamağın qarşısını almaq üçün
    $table->unique(['blocker_id', 'blocked_id']);

    // Tarix sütunları
    $table->timestamps();
    $table->softDeletes();
});
```

## 🔧 Service Metodları

### MessagingService
```php
// Söhbət əməliyyatları
getAllConversations(array $filters = []): LengthAwarePaginator
getUserConversations(int $userId, ?string $status = null): Collection
getConversationByUuid(string $uuid): Conversation
createConversation(int $creatorId, int $receiverId, ...): Conversation
togglePin(string $uuid): Conversation
archiveConversation(string $uuid): Conversation
unArchiveConversation(string $uuid): Conversation
blockConversation(string $uuid, ?string $reason = null): Conversation
unblockConversation(string $uuid): Conversation

// Mesaj əməliyyatları
createMessage(int $conversationId, int $senderId, ...): Message
createSystemMessage(int $conversationId, string $content): Message
getMessagesByConversationId(int $conversationId, ...): LengthAwarePaginator
markMessageAsRead(string $messageUuid): Message
markMultipleAsRead(array $messageUuids): void
markConversationAsRead(int $conversationId, int $userId): void
editMessage(string $messageUuid, string $newContent): Message
deleteMessage(string $messageUuid, bool $forEveryone = false): bool

// Statistika və hesabatlar
getMessageStatistics(int $conversationId): array
getConversationTypeStatistics(): array
getConversationStatusStatistics(): array
getConversationsByDateStatistics(int $days = 30): array
getMostActiveUsers(int $limit = 10): array
getUserUnreadMessageCount(int $userId): int

// Toplu mesaj əməliyyatları
sendBulkMessage(string $content, ?array $userIds = null, ...): array
```

### İstifadə nümunələri:
```php
$messagingService = app(MessagingService::class);

// Söhbət yaratmaq
$conversation = $messagingService->createConversation(
    creatorId: 1,
    receiverId: 2,
    type: ConversationTypeEnum::PRIVATE
);

// Mesaj göndərmək
$message = $messagingService->createMessage(
    conversationId: $conversation->id,
    senderId: 1,
    content: 'Salam, necəsən?',
    type: MessageTypeEnum::TEXT
);

// Şəkil mesajı göndərmək
$message = $messagingService->createMessage(
    conversationId: $conversation->id,
    senderId: 1,
    content: 'Bax, bu yeni şəkildir',
    type: MessageTypeEnum::IMAGE,
    attachments: [
        [
            'id' => 'img_123',
            'type' => 'image',
            'name' => 'photo.jpg',
            'path' => '/storage/uploads/images/photo.jpg',
            'size' => 1048576,
            'mime_type' => 'image/jpeg'
        ]
    ]
);

// Mesajları oxundu kimi işarələmək
$messagingService->markConversationAsRead($conversation->id, $userId);

// Söhbəti pinləmək
$messagingService->togglePin($conversation->uuid);
```

## 🔗 API Endpoints

### İstifadəçi API (Frontend)
```http
GET    /api/app/messaging/conversations           # Bütün söhbətlər
GET    /api/app/messaging/conversations/{uuid}    # Söhbət detayları
GET    /api/app/messaging/conversations/{uuid}/messages  # Söhbətdəki mesajlar
POST   /api/app/messaging/send                    # Mesaj göndərmək
POST   /api/app/messaging/conversations           # Yeni söhbət yaratmaq
POST   /api/app/messaging/conversations/{uuid}/pin  # Söhbəti pinləmək
POST   /api/app/messaging/conversations/{uuid}/archive  # Söhbəti arxivləşdirmək
POST   /api/app/messaging/conversations/{uuid}/unArchive  # Arxivdən çıxarmaq
POST   /api/app/messaging/mark-as-read            # Mesajları oxundu kimi işarələmək
GET    /api/app/messaging/search-users            # İstifadəçi axtarışı
GET    /api/app/messaging/unread-count            # Oxunmamış mesaj sayı
POST   /api/app/messaging/block-user              # İstifadəçini bloklamaq
POST   /api/app/messaging/unblock-user            # İstifadəçi blokunu açmaq
GET    /api/app/messaging/blocked-users           # Bloklanmış istifadəçilər
```

### Admin API
```http
GET    /api/admin/messaging/dashboard            # Mesajlaşma statistikası
GET    /api/admin/messaging/conversations        # Bütün söhbətlər
GET    /api/admin/messaging/conversations/{uuid} # Söhbət detayları
GET    /api/admin/messaging/conversations/{uuid}/messages # Mesajlar
GET    /api/admin/messaging/messages             # Bütün mesajlar
POST   /api/admin/messaging/conversations/{uuid}/block    # Söhbəti bloklamaq
POST   /api/admin/messaging/conversations/{uuid}/unblock  # Blokdan çıxarmaq
POST   /api/admin/messaging/conversations/{uuid}/system-message # Sistem mesajı
DELETE /api/admin/messaging/messages/{uuid}      # Mesajı silmək
POST   /api/admin/messaging/bulk-message         # Toplu mesaj göndərmək
```

## 💻 API İstifadə Nümunələri

### Söhbət yaratmaq
```bash
curl -X POST "/api/app/messaging/conversations" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "receiver_id": 2,
    "content": "Salam, tanış olmaq istəyirəm!",
    "type": "private"
}'
```

Response:
```json
{
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "type": "private",
    "status": "active",
    "is_pinned": false,
    "last_activity_at": "2025-01-15T12:00:00Z",
    "messages_count": 1,
    "unread_count": 0,
    "other_participant": {
        "id": 2,
        "name": "Elşən",
        "surname": "Əliyev",
        "avatar": "/storage/avatars/user2.jpg"
    },
    "last_message": {
        "content": "Salam, tanış olmaq istəyirəm!",
        "sent_at": "2025-01-15T12:00:00Z",
        "sender_id": 1
    }
}
```

### Mesaj göndərmək
```bash
curl -X POST "/api/app/messaging/send" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "conversation_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "content": "Növbəti görüşümüz nə vaxt olacaq?",
    "type": "text"
}'
```

### Şəkil mesajı göndərmək
```bash
curl -X POST "/api/app/messaging/send" \
-H "Authorization: Bearer TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "conversation_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "content": "Yeni şəkil göndərirəm",
    "type": "image",
    "attachments": [
        {
            "id": "img_123",
            "type": "image",
            "name": "photo.jpg",
            "path": "/storage/uploads/images/photo.jpg",
            "size": 1048576,
            "mime_type": "image/jpeg"
        }
    ]
}'
```

### Admin toplu mesaj göndərmək
```bash
curl -X POST "/api/admin/messaging/bulk-message" \
-H "Authorization: Bearer ADMIN_TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "content": "Platformamızda yeni xüsusiyyətlər! Profilinizi yeniləyin.",
    "type": "system",
    "filters": {
        "status": "active",
        "registered_from": "2024-01-01"
    }
}'
```

### Söhbəti bloklamaq (Admin)
```bash
curl -X POST "/api/admin/messaging/conversations/550e8400-e29b-41d4-a716-446655440000/block" \
-H "Authorization: Bearer ADMIN_TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "reason": "Platformanın qaydalarını pozduğunuz üçün söhbət bloklanmışdır."
}'
```

## 📋 Model Metodları və Enum-lar

### Conversation Status Workflow
```php
// app/Enums/ConversationStatusEnum.php
enum ConversationStatusEnum
{
    const ACTIVE = 'active';     // Aktiv söhbət
    const ARCHIVED = 'archived'; // Arxivləşdirilmiş
    const BLOCKED = 'blocked';   // Bloklanmış
}
```

### Conversation Types
```php
// app/Enums/ConversationTypeEnum.php
enum ConversationTypeEnum
{
    const PRIVATE = 'private';   // Şəxsi söhbət
    const LISTING = 'listing';   // Elan ilə əlaqəli
    const SUPPORT = 'support';   // Dəstək söhbəti
    const SYSTEM = 'system';     // Sistem bildirişləri
}
```

### Message Status
```php
// app/Enums/MessageStatusEnum.php
enum MessageStatusEnum
{
    const SENT = 'sent';           // Göndərildi
    const DELIVERED = 'delivered'; // Çatdırıldı
    const READ = 'read';           // Oxundu
}
```

### Message Types
```php
// app/Enums/MessageTypeEnum.php
enum MessageTypeEnum
{
    const TEXT = 'text';      // Mətn mesajı
    const IMAGE = 'image';    // Şəkil
    const FILE = 'file';      // Fayl
    const VOICE = 'voice';    // Səs yazısı
    const SYSTEM = 'system';  // Sistem mesajı
}
```

### Conversation Model Metodları
```php
class Conversation extends BaseModel
{
    // Əlaqələr
    public function messages(): HasMany
    public function creator(): BelongsTo
    public function receiver(): BelongsTo
    public function conversationable(): MorphTo
    public function lastMessage(): HasOne
    
    // Status dəyişiklikləri
    public function markAsArchived(): void
    public function markAsActive(): void
    public function togglePin(): void
    
    // Yardımçı metodlar
    public function updateLastActivity(): void
    public function updateMetaData(array $data): void
    public function getParticipant(bool $opposite = false): User
    public function getUnreadCount(int $userId): int
}
```

### Message Model Metodları
```php
class Message extends BaseModel
{
    // Əlaqələr
    public function conversation(): BelongsTo
    public function sender(): BelongsTo
    
    // Status metodları
    public function markAsDelivered(): void
    public function markAsRead(): void
    
    // Yardımçı metodlar
    public function isFromSender(int $userId): bool
}
```

### UserBlock Model Metodları
```php
class UserBlock extends BaseModel
{
    // Əlaqələr
    public function blocker(): BelongsTo
    public function blocked(): BelongsTo
    
    // Yardımçı metodlar
    public function isActive(): bool
    public function canMessage(): bool
}
```

### Praktik İstifadə
```php
// Söhbət yaratmaq
$conversation = Conversation::create([
    'creator_id' => $user1->id,
    'receiver_id' => $user2->id,
    'type' => ConversationTypeEnum::PRIVATE,
    'status' => ConversationStatusEnum::ACTIVE,
    'last_activity_at' => now()
]);

// Mesaj göndərmək
$message = $conversation->messages()->create([
    'sender_id' => $user1->id,
    'type' => MessageTypeEnum::TEXT,
    'content' => 'Salam, necəsən?',
    'status' => MessageStatusEnum::SENT
]);

// Mesajı oxundu kimi işarələmək
$message->markAsRead();

// İstifadəçi bloklamaq
UserBlock::create([
    'blocker_id' => $user1->id,
    'blocked_id' => $user2->id,
    'reason' => 'Narahat edici mesajlar'
]);

// Söhbəti pinləmək
$conversation->togglePin();

// Söhbətdəki oxunmamış mesaj sayını əldə etmək
$unreadCount = $conversation->getUnreadCount($userId);
```

## 🌱 Seeding Sistemi

### MessagingSystemSeeder
Test datası yaradır:

```bash
php artisan db:seed --class=MessagingSystemSeeder
```

**Yaradılan məlumatlar:**
- İstifadəçilər arası təsadüfi söhbətlər
- Hər söhbətdə 3-20 arası mesaj
- Müxtəlif tip mesajlar (mətn, şəkil, fayl, səs)
- İstifadəçilər arası bloklar
- Müxtəlif status və növlərdə söhbətlər

**Nümunə mesaj məzmunları:**
```php
$phrases = [
    'Salam, necəsən?',
    'Görüşə bilərik?',
    'Sabah vaxtın var?',
    'Təşəkkür edirəm',
    'Çox gözəl',
    'Bəli, mümkündür',
    'Mənim üçün uyğundur',
    'Daha sonra danışaq',
    'Bu gün hava gözəldir',
    'Təcili cavab yazarsan?'
];
```

## 🔍 Filter Sistemi

### MessagingFilter
```php
// Mövcud filterlər
'q' => 'search_term'  # Ümumi axtarış
'status' => 'active|archived|blocked'  # Status filter
'type' => 'private|listing|support|system'  # Söhbət növü
'user_id' => 'user_id'  # İstifadəçi ID
'date_range' => ['from' => '...', 'to' => '...']  # Tarix aralığı
'is_system' => true|false  # Sistem mesajları
'conversation_id' => 'id'  # Söhbət ID
'sender_id' => 'id'  # Göndərən ID
```

### Filter istifadəsi:
```bash
# Aktiv söhbətlər
GET /api/admin/messaging/conversations?status=active

# Müəyyən istifadəçinin söhbətləri
GET /api/admin/messaging/conversations?user_id=123

# Sistem mesajları
GET /api/admin/messaging/messages?is_system=true

# Mətn axtarışı
GET /api/admin/messaging/conversations?q=salam
```

## 🚀 Performance Optimizasiyası

### Repository Optimizasiyaları
```php
// İstifadəçinin söhbətlərini effektiv əldə etmək
public function getUserConversations(int $userId, ?string $status = null): Collection
{
    $query = $this->model->newQuery()
        ->where(function($query) use ($userId) {
            $query->where('creator_id', $userId)
                ->orWhere('receiver_id', $userId);
        })
        ->with(['lastMessage', 'creator', 'receiver']);

    if ($status) {
        $query->where('status', $status);
    } else {
        $query->where('status', ConversationStatusEnum::ACTIVE);
    }

    return $query->orderBy('is_pinned', 'desc')
        ->orderBy('last_activity_at', 'desc')
        ->get();
}
```

### Əlaqəli Yükləmə
```php
// Söhbət əldə edilərkən əlaqəli məlumatları da yükləmək
$conversation = $this->model->newQuery()
    ->where('uuid', $uuid)
    ->with(['lastMessage', 'creator', 'receiver'])
    ->firstOrFail();
```

### Cache İstifadəsi
```php
// İstifadəçinin oxunmamış mesaj sayını cache-də saxlamaq
public function getUserUnreadMessageCount(int $userId): int
{
    return Cache::remember("user_{$userId}_unread_count", 300, function() use ($userId) {
        return $this->messageModel
            ->whereHas('conversation', function($query) use ($userId) {
                $query->where(function($q) use ($userId) {
                    $q->where('creator_id', $userId)
                        ->orWhere('receiver_id', $userId);
                })
                    ->where('status', ConversationStatusEnum::ACTIVE);
            })
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    });
}
```

## 🧪 Testing

### Unit Test Nümunəsi
```php
public function test_can_create_conversation()
{
    $creator = User::factory()->create();
    $receiver = User::factory()->create();
    
    $messagingService = app(MessagingService::class);
    $conversation = $messagingService->createConversation(
        $creator->id,
        $receiver->id
    );
    
    $this->assertDatabaseHas('conversations', [
        'creator_id' => $creator->id,
        'receiver_id' => $receiver->id,
        'status' => ConversationStatusEnum::ACTIVE
    ]);
}

public function test_can_send_message()
{
    $conversation = Conversation::factory()->create();
    
    $messagingService = app(MessagingService::class);
    $message = $messagingService->createMessage(
        $conversation->id,
        $conversation->creator_id,
        'Test message',
        MessageTypeEnum::TEXT
    );
    
    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'sender_id' => $conversation->creator_id,
        'content' => 'Test message',
        'type' => MessageTypeEnum::TEXT
    ]);
}

public function test_user_cannot_message_when_blocked()
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    UserBlock::create([
        'blocker_id' => $user1->id,
        'blocked_id' => $user2->id
    ]);
    
    $userBlockService = app(UserBlockService::class);
    [$canMessage, $blockedBy] = $userBlockService->canMessage($user2->id, $user1->id);
    
    $this->assertFalse($canMessage);
    $this->assertEquals($user1->id, $blockedBy);
}
```

### Feature Test Nümunəsi
```php
public function test_api_can_create_conversation()
{
    $user = User::factory()->create();
    $receiver = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/app/messaging/conversations', [
            'receiver_id' => $receiver->id,
            'content' => 'Hello, testing!',
            'type' => 'private'
        ]);
        
    $response->assertStatus(200)
        ->assertJsonStructure([
            'uuid',
            'type',
            'status',
            'other_participant',
            'last_message'
        ]);
    
    $this->assertDatabaseHas('messages', [
        'content' => 'Hello, testing!',
        'sender_id' => $user->id
    ]);
}

public function test_api_cannot_message_blocked_user()
{
    $user = User::factory()->create();
    $blockedUser = User::factory()->create();
    
    // Blok yaratmaq
    UserBlock::create([
        'blocker_id' => $user->id,
        'blocked_id' => $blockedUser->id
    ]);
    
    // Söhbət yaratmağa çalışmaq
    $response = $this->actingAs($user)
        ->postJson('/api/app/messaging/conversations', [
            'receiver_id' => $blockedUser->id,
            'content' => 'Hello blocked user!',
            'type' => 'private'
        ]);
        
    $response->assertStatus(409);
}
```

## 📊 Admin Dashboard

Admin panel üçün əsas bölmələr:

1. **Ümumi Statistika**
    - Söhbət növlərinə görə bölgü
    - Status statistikaları
    - Zaman aralığına görə aktivlik

2. **İstifadəçi Aktivliyi**
    - Ən aktiv istifadəçilər
    - Mesaj sayları və tendensiyalar
    - Aktiv/Passiv istifadəçi nisbəti

3. **Söhbət İdarəetməsi**
    - Söhbətlərin listəsi və filtrlənməsi
    - Söhbət detayları və mesajlar
    - Söhbətləri bloklamaq/açmaq funksionallığı

4. **Bulk Mesaj Göndərmə**
    - İstifadəçi filtrlərinə görə seçim
    - Mesaj növü seçimi (sistem, text)
    - Göndərmə statusu və nəticələri

5. **Monitoring**
    - Sistem mesajlarının izlənməsi
    - Problem və xəta bildirişləri
    - Performans göstəriciləri

## 🔄 İş Prosesi (Workflow)

### İstifadəçi Mesajlaşma Prosesi
1. İstifadəçi söhbət başladır (yeni söhbət yaradır)
2. Mesajlar göndərir (mətn, şəkil, fayl, səs)
3. Qarşı tərəf mesajları alır və oxuyur (status yenilənir)
4. Qarşı tərəf cavab göndərir
5. İstifadəçi söhbəti idarə edir (pinləmək, arxivləşdirmək, bloklamaq)

### Admin Moderasiya Prosesi
1. Admin söhbətləri izləyir
2. Problematik söhbəti aşkar edir
3. Söhbət detaylarını yoxlayır
4. Zəruri hallarda söhbəti bloklayır və səbəb qeyd edir
5. Sistem mesajı ilə iştirakçılara məlumat verir

### Mesaj Status İzləmə Prosesi
1. İstifadəçi mesaj göndərir (status: `sent`)
2. Mesaj qarşı tərəfə çatdırılır (status: `delivered`)
3. Qarşı tərəf mesajı oxuyur (status: `read`)
4. Tarixlər qeyd olunur (`delivered_at`, `read_at`)

## 📑 Asılılıqlar və Tələblər

- PHP 8.2+
- Laravel 12.0
- MySQL/PostgreSQL
- WebSocket server (real-time funksionallığı üçün)
- File storage system (şəkil, fayl və səs mesajları üçün)

## 🔐 Təhlükəsizlik

- Söhbət iştirakçıları yalnız öz söhbətlərinə giriş əldə edə bilər
- İstifadəçilər bir-birlərini bloklaya bilər
- Admin yalnız xüsusi icazələrlə söhbətlərə müdaxilə edə bilər
- Silmə əməliyyatları soft delete ilə reallaşdırılır (data saxlanılır)
- Mesaj yükləmələri üçün fayl məhdudiyyətləri və təhlükəsizlik yoxlamaları

## 🔄 Yenilənmə Qeydləri

**v1.0.0** (2025-01-15)
- İlkin versiya
- İstifadəçilər arası mesajlaşma
- Fayl əlavələri dəstəyi
- Status izləmə sistemi
- Admin monitorinq paneli

## 📞 Dəstək

Əlavə suallar və texniki dəstək üçün:
- Email: [support@example.com](mailto:support@example.com)
- Issue tracker: [Github Issues](https://github.com/example/messaging-service/issues)

## 📄 Lisenziya

Bu layihə MIT lisenziyası altında lisenziyalanıb - ətraflı məlumat üçün [LICENSE](LICENSE) faylına baxın.
