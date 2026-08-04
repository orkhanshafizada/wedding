# Notification Service Documentation

## 📋 Ümumi Məlumat

Notification Service Laravel 12 layihəsində çoxkanallı (multi-channel) bildiriş sistemini idarə edir. Bu service email, push notification, telegram və digər kanallar vasitəsilə bildirişlər göndərə bilir, həmçinin bildiriş tarixçəsini və statistikasını idarə edir.

## 🚀 Xüsusiyyətlər

- ✅ **Multi-Channel Support** - Email, Push, Telegram və digər kanallar
- ✅ **Polymorphic Notifications** - İstənilən model üçün bildiriş
- ✅ **Scheduled Notifications** - Planlaşdırılmış bildirişlər
- ✅ **Priority System** - Aşağı, normal, yüksək prioritet
- ✅ **Delivery Tracking** - Göndərmə nəticələrinin izlənməsi
- ✅ **Retry Mechanism** - Uğursuz göndərmələrin yenidən cəhdi
- ✅ **Bulk Operations** - Kütləvi bildiriş əməliyyatları
- ✅ **Template System** - Dinamik placeholder sistemi
- ✅ **Admin Dashboard** - Tam administrativ panel
- ✅ **Real-time Stats** - Canlı statistika və analitika

## 📊 Məlumat Bazası Strukturu

### `notifications` cədvəli
```sql
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) NOT NULL UNIQUE,
    notifiable_type VARCHAR(255) NOT NULL,    -- Model class (User, Company və s.)
    notifiable_id BIGINT UNSIGNED NOT NULL,   -- Model ID
    type VARCHAR(255) NOT NULL,               -- Notification növü
    data JSON NOT NULL,                       -- Notification məlumatları
    read_at TIMESTAMP NULL,                   -- Oxunma tarixi
    send_at TIMESTAMP NULL,                   -- Planlaşdırılma tarixi
    priority VARCHAR(255) DEFAULT 'normal',   -- Prioritet
    status VARCHAR(255) DEFAULT 'pending',    -- Status
    created_by BIGINT UNSIGNED NULL,          -- Yaradıcı
    updated_by BIGINT UNSIGNED NULL,          -- Yeniləyici
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_notifiable (notifiable_type, notifiable_id),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_read_at (read_at),
    INDEX idx_send_at (send_at)
);
```

### `notification_deliveries` cədvəli
```sql
CREATE TABLE notification_deliveries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    notification_id BIGINT UNSIGNED NOT NULL,
    channel VARCHAR(255) NOT NULL,            -- Kanal adı (email, push, telegram)
    success BOOLEAN NOT NULL,                 -- Uğurlu/uğursuz
    error TEXT NULL,                         -- Xəta mesajı
    delivered_at TIMESTAMP NOT NULL,         -- Göndərmə tarixi
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    UNIQUE KEY unique_notification_channel (notification_id, channel),
    INDEX idx_channel (channel),
    INDEX idx_success (success),
    INDEX idx_delivered_at (delivered_at)
);
```

## 🎯 Notification Types & Priorities

### Notification Növləri (NotificationTypeEnum)
```php
// Sistem bildirişləri
SYSTEM_ALERT = 'system_alert'
SYSTEM_MAINTENANCE = 'system_maintenance'

// İstifadəçi bildirişləri  
USER_WELCOME = 'user_welcome'
USER_VERIFICATION = 'user_verification'
USER_PASSWORD_RESET = 'user_password_reset'

// Elan bildirişləri
LISTING_APPROVED = 'listing_approved'
LISTING_REJECTED = 'listing_rejected'
LISTING_EXPIRED = 'listing_expired'

// Ödəniş bildirişləri
PAYMENT_COMPLETED = 'payment_completed'
PAYMENT_FAILED = 'payment_failed'

// Referral bildirişləri
REFERRAL_BONUS_ADDED = 'referral_bonus_added'
REFERRAL_COMPLETED = 'referral_completed'
```

### Prioritet Səviyyələri (NotificationPriorityEnum)
```php
LOW = 'low'       // Aşağı prioritet
NORMAL = 'normal' // Normal prioritet (default)
HIGH = 'high'     // Yüksək prioritet
```

## 🔗 API Endpoints

### Admin Panel Endpoints

| HTTP Method | Endpoint | Açıqlama | Permission |
|-------------|----------|----------|------------|
| `GET` | `/api/admin/notifications` | Bildiriş siyahısı və statistika | `notification_read` |
| `POST` | `/api/admin/notifications` | Sistem bildirişi yaratma | `notification_create` |
| `DELETE` | `/api/admin/notifications/{id}` | Bildiriş silmə | `notification_delete` |
| `POST` | `/api/admin/notifications/bulk-delete` | Çoxlu bildiriş silmə | `notification_delete` |

### User Interface Endpoints

| HTTP Method | Endpoint | Açıqlama | Auth Required |
|-------------|----------|----------|---------------|
| `GET` | `/api/user/notifications` | İstifadəçi bildirişləri | ✅ |
| `POST` | `/api/user/notifications/{id}/read` | Bildirişi oxunmuş işarələmə | ✅ |
| `POST` | `/api/user/notifications/mark-all-read` | Hamısını oxunmuş işarələmə | ✅ |
| `GET` | `/api/user/notifications/stats` | İstifadəçi statistikası | ✅ |

## 💻 İstifadə Nümunələri

### Sadə Bildiriş Göndərmə

```php
use App\Services\Module\NotificationService;
use App\Enums\NotificationTypeEnum;

// Service-i əldə edirik
$notificationService = app(NotificationService::class);

// İstifadəçiyə bildiriş göndəririk
$user = User::find(1);
$notification = $notificationService->send(
    notifiable: $user,
    type: NotificationTypeEnum::USER_WELCOME,
    data: [
        'user_name' => $user->name,
        'welcome_bonus' => 50
    ]
);
```

### Planlaşdırılmış Bildiriş

```php
use Carbon\Carbon;

// 1 saat sonra göndərmək üçün planlaşdırırıq
$scheduledNotification = $notificationService->schedule(
    notifiable: $user,
    type: NotificationTypeEnum::LISTING_EXPIRED,
    sendAt: now()->addHours(1),
    data: [
        'listing_title' => '2 otaqlı mənzil',
        'expired_date' => now()->addDays(7)->format('d.m.Y')
    ]
);
```

### Çoxlu İstifadəçiyə Bildiriş

```php
// Aktiv istifadəçilərə sistem bildirişi
$activeUsers = User::where('status', 'active')->get();

$notificationService->sendMultiple(
    notifiables: $activeUsers,
    type: NotificationTypeEnum::SYSTEM_MAINTENANCE,
    data: [
        'maintenance_date' => '15.01.2025',
        'duration' => '2 saat',
        'services_affected' => ['Ödəniş sistemi', 'Mesajlaşma']
    ]
);
```

### Admin Panel - Sistem Bildirişi

```php
// Admin panel üçün sistem bildirişi yaratma
$notificationData = [
    'user_ids' => [1, 2, 3, 4],
    'title' => 'Yeni xüsusiyyət',
    'message' => 'Platformaya yeni axtarış filtirləri əlavə edildi!',
    'action_url' => 'https://example.com/new-features',
    'send_at' => '2025-01-20 10:00:00' // İsteğe bağlı
];

$notifications = $notificationService->createSystemNotifications($notificationData);
```

## 🌐 Frontend İstifadəsi

### React/Vue Component

```javascript
// NotificationService.js
class NotificationService {
    constructor(token) {
        this.token = token;
        this.baseURL = '/api';
    }

    async getNotifications(status = 'all', perPage = 15) {
        const response = await fetch(`${this.baseURL}/user/notifications`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status, per_page: perPage })
        });

        return await response.json();
    }

    async markAsRead(notificationId) {
        const response = await fetch(`${this.baseURL}/user/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        return await response.json();
    }

    async markAllAsRead() {
        const response = await fetch(`${this.baseURL}/user/notifications/mark-all-read`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        return await response.json();
    }

    async getStats() {
        const response = await fetch(`${this.baseURL}/user/notifications/stats`, {
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Accept': 'application/json'
            }
        });

        return await response.json();
    }
}

export default NotificationService;
```

### React Notification Component

```jsx
// NotificationList.jsx
import React, { useState, useEffect } from 'react';
import NotificationService from './NotificationService';

const NotificationList = () => {
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [loading, setLoading] = useState(true);
    
    const notificationService = new NotificationService(
        localStorage.getItem('auth_token')
    );

    useEffect(() => {
        fetchNotifications();
    }, []);

    const fetchNotifications = async () => {
        try {
            const response = await notificationService.getNotifications();
            setNotifications(response.data);
            setUnreadCount(response.unread_count);
        } catch (error) {
            console.error('Bildirişlər yüklənmədi:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleMarkAsRead = async (notificationId) => {
        try {
            const response = await notificationService.markAsRead(notificationId);
            setUnreadCount(response.unread_count);
            
            // Local state-i yeniləyirik
            setNotifications(prev => 
                prev.map(notification => 
                    notification.id === notificationId 
                        ? { ...notification, read_at: new Date().toISOString() }
                        : notification
                )
            );
        } catch (error) {
            console.error('Bildiriş oxunmuş işarələnmədi:', error);
        }
    };

    const handleMarkAllAsRead = async () => {
        try {
            await notificationService.markAllAsRead();
            setUnreadCount(0);
            
            setNotifications(prev => 
                prev.map(notification => ({
                    ...notification,
                    read_at: notification.read_at || new Date().toISOString()
                }))
            );
        } catch (error) {
            console.error('Bildirişlər oxunmuş işarələnmədi:', error);
        }
    };

    if (loading) return <div>Yüklənir...</div>;

    return (
        <div className="notification-list">
            <div className="notification-header">
                <h3>Bildirişlər ({unreadCount} oxunmamış)</h3>
                {unreadCount > 0 && (
                    <button onClick={handleMarkAllAsRead} className="btn-mark-all">
                        Hamısını oxunmuş işarələ
                    </button>
                )}
            </div>

            {notifications.length === 0 ? (
                <div className="no-notifications">
                    Bildiriş yoxdur
                </div>
            ) : (
                <div className="notifications">
                    {notifications.map(notification => (
                        <div 
                            key={notification.id}
                            className={`notification-item ${!notification.read_at ? 'unread' : ''}`}
                            onClick={() => !notification.read_at && handleMarkAsRead(notification.id)}
                        >
                            <div className="notification-content">
                                <h4>{notification.data.title}</h4>
                                <p>{notification.data.message}</p>
                                <span className="notification-time">
                                    {new Date(notification.created_at).toLocaleString('az-AZ')}
                                </span>
                            </div>
                            <div className="notification-priority">
                                <span className={`priority-badge ${notification.priority}`}>
                                    {notification.priority_text}
                                </span>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default NotificationList;
```

### Admin Panel - Sistem Bildirişi

```jsx
// AdminNotificationForm.jsx
import React, { useState } from 'react';

const AdminNotificationForm = () => {
    const [formData, setFormData] = useState({
        user_ids: [],
        title: '',
        message: '',
        action_url: '',
        send_at: ''
    });

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        try {
            const response = await fetch('/api/admin/notifications', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            if (response.ok) {
                const result = await response.json();
                alert('Sistem bildirişi uğurla yaradıldı!');
                // Form-u sıfırlayırıq
                setFormData({
                    user_ids: [],
                    title: '',
                    message: '',
                    action_url: '',
                    send_at: ''
                });
            } else {
                const error = await response.json();
                console.error('Xəta:', error);
            }
        } catch (error) {
            console.error('Network xətası:', error);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="admin-notification-form">
            <div className="form-group">
                <label>İstifadəçilər:</label>
                <select 
                    multiple 
                    value={formData.user_ids}
                    onChange={(e) => setFormData({
                        ...formData, 
                        user_ids: Array.from(e.target.selectedOptions, option => option.value)
                    })}
                    required
                >
                    {/* İstifadəçi siyahısı buraya */}
                </select>
            </div>

            <div className="form-group">
                <label>Başlıq:</label>
                <input
                    type="text"
                    value={formData.title}
                    onChange={(e) => setFormData({...formData, title: e.target.value})}
                    required
                />
            </div>

            <div className="form-group">
                <label>Mesaj:</label>
                <textarea
                    value={formData.message}
                    onChange={(e) => setFormData({...formData, message: e.target.value})}
                    required
                />
            </div>

            <div className="form-group">
                <label>Action URL (isteğe bağlı):</label>
                <input
                    type="url"
                    value={formData.action_url}
                    onChange={(e) => setFormData({...formData, action_url: e.target.value})}
                />
            </div>

            <div className="form-group">
                <label>Planlaşdırılma tarixi (isteğe bağlı):</label>
                <input
                    type="datetime-local"
                    value={formData.send_at}
                    onChange={(e) => setFormData({...formData, send_at: e.target.value})}
                />
            </div>

            <button type="submit" className="btn-submit">
                Bildiriş Göndər
            </button>
        </form>
    );
};

export default AdminNotificationForm;
```

## 🛠 Service Metodları

### NotificationService Class

```php
namespace App\Services\Module;

class NotificationService extends BaseCrudService
{
    /**
     * Notification kanalı qeydiyyatı
     */
    public function registerChannel(string $name, NotificationChannel $channel): void

    /**
     * Bildiriş göndərmə
     */
    public function send(
        Model $notifiable,
        string $type,
        array $data = [],
        ?string $priority = null,
        ?Carbon $sendAt = null
    ): Notification

    /**
     * Çoxlu bildiriş göndərmə
     */
    public function sendMultiple(Collection $notifiables, string $type, array $data = []): void

    /**
     * Planlaşdırılmış bildiriş
     */
    public function schedule(
        Model $notifiable,
        string $type,
        Carbon $sendAt,
        array $data = []
    ): Notification

    /**
     * Planlaşdırılmış bildirişi ləğv etmə
     */
    public function cancelScheduled(int $id): void

    /**
     * Bildirişi oxunmuş işarələmə
     */
    public function markAsRead(int $id): void

    /**
     * Bütün bildirişləri oxunmuş işarələmə
     */
    public function markAllAsRead(Model $notifiable): void

    /**
     * Oxunmamış bildiriş sayı
     */
    public function getUnreadCount(Model $notifiable): int

    /**
     * İstifadəçi statistikası
     */
    public function getStats(Model $notifiable): array

    /**
     * Sistem bildirişi yaratma (Admin panel)
     */
    public function createSystemNotifications(array $data): array

    /**
     * İstifadəçi bildirişləri (pagination)
     */
    public function getUserNotifications(
        Model $user,
        string $status = 'all',
        int $perPage = 15
    ): LengthAwarePaginator

    /**
     * Admin statistikası
     */
    public function getAdminStats(): array
}
```

## 🏗️ Repository Metodları

### NotificationRepository Class

```php
namespace App\Repositories\Module;

class NotificationRepository extends BaseRepository
{
    /**
     * Göndərmə məlumatlarını qeydə alma
     */
    public function logDelivery(
        Notification $notification,
        string $channel,
        bool $success,
        ?string $error = null
    ): NotificationDelivery

    /**
     * Planlaşdırılmış bildirişlər
     */
    public function getScheduledNotifications(): Collection

    /**
     * Gözləyən bildirişlər
     */
    public function getPendingNotifications(): Collection

    /**
     * Uğursuz bildirişlər
     */
    public function getFailedNotifications(): Collection

    /**
     * Bütün bildirişləri oxunmuş işarələmə
     */
    public function markAllAsRead(Model $notifiable): void

    /**
     * Oxunmamış say
     */
    public function getUnreadCount(Model $notifiable): int

    /**
     * Köhnə bildirişləri təmizləmə
     */
    public function cleanOldNotifications(int $days = 30): int

    /**
     * Göndərmə statistikaları
     */
    public function getDeliveryStats(): array

    /**
     * Admin statistikaları
     */
    public function getAdminStatistics(): array

    /**
     * Çoxlu silmə
     */
    public function bulkDelete(array $ids): bool
}
```

## 📡 Notification Channels

### Email Channel

```php
// App\Services\Notification\Channels\EmailChannel
class EmailChannel implements NotificationChannel
{
    public function send($notifiable, Notification $notification): bool
    {
        $emailData = $notification->data;
        
        try {
            Mail::to($notifiable->email)->send(
                new DynamicNotificationMail($emailData)
            );
            return true;
        } catch (Exception $e) {
            Log::error('Email notification failed: ' . $e->getMessage());
            return false;
        }
    }
}
```

### Push Notification Channel

```php
// App\Services\Notification\Channels\PushChannel
class PushChannel implements NotificationChannel
{
    public function send($notifiable, Notification $notification): bool
    {
        $pushData = $notification->data;
        
        try {
            // FCM və ya digər push service istifadə
            $this->pushService->sendToUser(
                $notifiable->id,
                $pushData['title'],
                $pushData['message'],
                $pushData
            );
            return true;
        } catch (Exception $e) {
            Log::error('Push notification failed: ' . $e->getMessage());
            return false;
        }
    }
}
```

### Telegram Channel

```php
// App\Services\Notification\Channels\TelegramChannel
class TelegramChannel implements NotificationChannel
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function send($notifiable, Notification $notification): bool
    {
        $telegramId = $notifiable->telegram_id;
        
        if (!$telegramId) {
            return false;
        }

        try {
            $this->telegram
                ->chat($telegramId)
                ->sendMessage($notification->data['message']);
                
            return true;
        } catch (Exception $e) {
            Log::error('Telegram notification failed: ' . $e->getMessage());
            return false;
        }
    }
}
```

## 🔄 Job və Queue Integration

### Scheduled Notification Job

```php
// App\Jobs\SendScheduledNotifications
class SendScheduledNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NotificationService $notificationService)
    {
        $scheduledNotifications = $notificationService
            ->getRepository()
            ->getScheduledNotifications();

        foreach ($scheduledNotifications as $notification) {
            // Bildirişi göndəririk
            $notificationService->sendToChannels(
                $notification->notifiable, 
                $notification
            );
            
            // send_at-ı sıfırlayırıq
            $notification->update(['send_at' => null]);
        }
    }
}
```

### Cron Job

```bash
# scheduler.php
Schedule::job(new SendScheduledNotifications)
    ->everyMinute()
    ->withoutOverlapping();
```

## 📊 Dashboard və Statistika

### Admin Dashboard Məlumatları

```php
// Admin panel statistika strukturu
[
    'summary' => [
        'total' => 15420,
        'unread' => 2150,
        'scheduled' => 45
    ],
    'by_priority' => [
        'low' => ['total' => 8500, 'unread' => 950],
        'normal' => ['total' => 6200, 'unread' => 1100],
        'high' => ['total' => 720, 'unread' => 100]
    ],
    'by_type' => [
        'user_welcome' => ['total' => 2400, 'unread' => 0],
        'system_alert' => ['total' => 156, 'unread' => 89],
        'listing_approved' => ['total' => 3200, 'unread' => 450]
    ],
    'delivery' => [
        'total' => ['sent' => 14800, 'successful' => 14200, 'failed' => 600],
        'by_channel' => [
            'email' => ['sent' => 12000, 'successful' => 11800, 'failed' => 200],
            'push' => ['sent' => 2500, 'successful' => 2100, 'failed' => 400],
            'telegram' => ['sent' => 300, 'successful' => 300, 'failed' => 0]
        ]
    ]
]
```

### JavaScript Dashboard Component

```jsx
// NotificationDashboard.jsx
import React, { useState, useEffect } from 'react';
import { Chart } from 'chart.js';

const NotificationDashboard = () => {
    const [stats, setStats] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchStats();
    }, []);

    const fetchStats = async () => {
        try {
            const response = await fetch('/api/admin/notifications', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            setStats(data.stats);
        } catch (error) {
            console.error('Statistika yüklənmədi:', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <div>Yüklənir...</div>;

    return (
        <div className="notification-dashboard">
            {/* Summary Cards */}
            <div className="stats-grid">
                <div className="stat-card">
                    <h3>Ümumi Bildiriş</h3>
                    <p className="stat-number">{stats.summary.total}</p>
                </div>
                <div className="stat-card">
                    <h3>Oxunmamış</h3>
                    <p className="stat-number unread">{stats.summary.unread}</p>
                </div>
                <div className="stat-card">
                    <h3>Planlaşdırılmış</h3>
                    <p className="stat-number scheduled">{stats.summary.scheduled}</p>
                </div>
            </div>

            {/* Delivery Stats */}
            <div className="delivery-stats">
                <h3>Göndərmə Statistikası</h3>
                <div className="delivery-grid">
                    {Object.entries(stats.delivery.by_channel).map(([channel, data]) => (
                        <div key={channel} className="delivery-card">
                            <h4>{channel.toUpperCase()}</h4>
                            <div className="delivery-info">
                                <p>Göndərildi: {data.sent}</p>
                                <p>Uğurlu: {data.successful}</p>
                                <p>Uğursuz: {data.failed}</p>
                                <div className="success-rate">
                                    Uğur oranı: {((data.successful / data.sent) * 100).toFixed(1)}%
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Type Distribution */}
            <div className="type-stats">
                <h3>Növlər üzrə Paylanma</h3>
                <div className="type-list">
                    {Object.entries(stats.by_type).map(([type, data]) => (
                        <div key={type} className="type-item">
                            <span className="type-name">{data.description}</span>
                            <span className="type-count">{data.total}</span>
                            {data.unread > 0 && (
                                <span className="unread-badge">{data.unread} oxunmamış</span>
                            )}
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
};

export default NotificationDashboard;
```

## 🧪 Testing

### Unit Tests

```php
// NotificationServiceTest.php
class NotificationServiceTest extends TestCase
{
    public function test_can_send_notification()
    {
        $user = User::factory()->create();
        
        $notification = $this->notificationService->send(
            $user,
            NotificationTypeEnum::USER_WELCOME,
            ['user_name' => $user->name]
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals('user_welcome', $notification->type);
        $this->assertArrayHasKey('user_name', $notification->data);
    }

    public function test_can_schedule_notification()
    {
        $user = User::factory()->create();
        $sendAt = now()->addHours(1);
        
        $notification = $this->notificationService->schedule(
            $user,
            NotificationTypeEnum::LISTING_EXPIRED,
            $sendAt
        );

        $this->assertEquals($sendAt->timestamp, $notification->send_at->timestamp);
    }

    public function test_can_mark_as_read()
    {
        $notification = Notification::factory()->create();
        
        $this->notificationService->markAsRead($notification->id);
        
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_can_send_multiple_notifications()
    {
        $users = User::factory()->count(3)->create();
        
        $this->notificationService->sendMultiple(
            $users,
            NotificationTypeEnum::SYSTEM_ALERT,
            ['message' => 'Test message']
        );

        $this->assertEquals(3, Notification::count());
    }

    public function test_placeholder_replacement()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        
        $notification = $this->notificationService->send(
            $user,
            NotificationTypeEnum::USER_WELCOME,
            [
                'user_name' => $user->name,
                'message' => 'Xoş gəldin {user_name}!'
            ]
        );

        $this->assertEquals('Xoş gəldin John Doe!', $notification->data['message']);
    }
}
```

### Feature Tests

```php
// NotificationControllerTest.php
class NotificationControllerTest extends TestCase
{
    public function test_admin_can_create_system_notification()
    {
        $admin = User::factory()->admin()->create();
        $users = User::factory()->count(2)->create();

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/notifications', [
                'user_ids' => $users->pluck('id')->toArray(),
                'title' => 'Test Notification',
                'message' => 'This is a test message'
            ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Sistem bildirişi uğurla yaradıldı']);

        $this->assertEquals(2, Notification::count());
    }

    public function test_user_can_get_their_notifications()
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/user/notifications');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'data', 'read_at', 'created_at']
                ],
                'total',
                'unread_count'
            ]);
    }

    public function test_user_can_mark_notification_as_read()
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/user/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJson(['message' => 'Bildiriş oxunmuş kimi işarələndi']);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_access_others_notifications()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $notification = Notification::factory()->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user2->id
        ]);

        $response = $this->actingAs($user1)
            ->postJson("/api/user/notifications/{$notification->id}/read");

        $response->assertStatus(404);
    }
}
```

### Channel Tests

```php
// EmailChannelTest.php
class EmailChannelTest extends TestCase
{
    public function test_email_channel_sends_successfully()
    {
        Mail::fake();
        
        $user = User::factory()->create();
        $notification = Notification::factory()->create([
            'data' => [
                'title' => 'Test Email',
                'message' => 'Test message content'
            ]
        ]);

        $channel = new EmailChannel();
        $result = $channel->send($user, $notification);

        $this->assertTrue($result);
        Mail::assertSent(DynamicNotificationMail::class);
    }

    public function test_email_channel_handles_failure()
    {
        Mail::shouldReceive('to')->andThrow(new Exception('SMTP Error'));
        
        $user = User::factory()->create();
        $notification = Notification::factory()->create();

        $channel = new EmailChannel();
        $result = $channel->send($user, $notification);

        $this->assertFalse($result);
    }
}
```

## 🔧 Konfiqurasiya

### Notification Konfigurasiyası

```php
// config/notifications.php
return [
    'channels' => [
        'email' => [
            'enabled' => env('NOTIFICATION_EMAIL_ENABLED', true),
            'from_address' => env('MAIL_FROM_ADDRESS'),
            'from_name' => env('MAIL_FROM_NAME'),
        ],
        'push' => [
            'enabled' => env('NOTIFICATION_PUSH_ENABLED', true),
            'fcm_server_key' => env('FCM_SERVER_KEY'),
            'fcm_project_id' => env('FCM_PROJECT_ID'),
        ],
        'telegram' => [
            'enabled' => env('NOTIFICATION_TELEGRAM_ENABLED', false),
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
            'api_url' => env('TELEGRAM_API_URL', 'https://api.telegram.org'),
        ]
    ],

    'retry' => [
        'max_attempts' => env('NOTIFICATION_MAX_RETRIES', 3),
        'delay_minutes' => env('NOTIFICATION_RETRY_DELAY', 5),
    ],

    'cleanup' => [
        'old_notifications_days' => env('NOTIFICATION_CLEANUP_DAYS', 30),
        'failed_deliveries_days' => env('NOTIFICATION_FAILED_CLEANUP_DAYS', 7),
    ],

    'rate_limit' => [
        'per_minute' => env('NOTIFICATION_RATE_LIMIT', 60),
        'per_hour' => env('NOTIFICATION_HOURLY_LIMIT', 1000),
    ]
];
```

### Environment Variables

```env
# Notification Settings
NOTIFICATION_EMAIL_ENABLED=true
NOTIFICATION_PUSH_ENABLED=true
NOTIFICATION_TELEGRAM_ENABLED=false

# Email Configuration
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Example App"

# Push Notification (FCM)
FCM_SERVER_KEY=your_fcm_server_key
FCM_PROJECT_ID=your_project_id

# Telegram Bot
TELEGRAM_BOT_TOKEN=your_bot_token

# Retry & Cleanup
NOTIFICATION_MAX_RETRIES=3
NOTIFICATION_RETRY_DELAY=5
NOTIFICATION_CLEANUP_DAYS=30

# Rate Limiting
NOTIFICATION_RATE_LIMIT=60
NOTIFICATION_HOURLY_LIMIT=1000
```

## 🚀 Performance Optimizasiyası

### Database İndekslər

```sql
-- Notification cədvəli üçün optimizasiya
CREATE INDEX idx_notifications_notifiable ON notifications(notifiable_type, notifiable_id);
CREATE INDEX idx_notifications_type ON notifications(type);
CREATE INDEX idx_notifications_status ON notifications(status);
CREATE INDEX idx_notifications_priority ON notifications(priority);
CREATE INDEX idx_notifications_read_at ON notifications(read_at);
CREATE INDEX idx_notifications_send_at ON notifications(send_at);
CREATE INDEX idx_notifications_created_at ON notifications(created_at);

-- Delivery cədvəli üçün optimizasiya
CREATE INDEX idx_deliveries_notification_id ON notification_deliveries(notification_id);
CREATE INDEX idx_deliveries_channel ON notification_deliveries(channel);
CREATE INDEX idx_deliveries_success ON notification_deliveries(success);
CREATE INDEX idx_deliveries_delivered_at ON notification_deliveries(delivered_at);
```

### Cache Strategiyası

```php
// Cache implementasiyası
class NotificationRepository extends BaseRepository
{
    protected bool $useCache = true;
    protected int $cacheMinutes = 15;

    public function getUnreadCount(Model $notifiable): int
    {
        $cacheKey = "unread_notifications_{$notifiable->getMorphClass()}_{$notifiable->id}";
        
        return Cache::remember($cacheKey, now()->addMinutes($this->cacheMinutes), function () use ($notifiable) {
            return $this->model
                ->where([
                    'notifiable_type' => get_class($notifiable),
                    'notifiable_id' => $notifiable->id
                ])
                ->whereNull('read_at')
                ->count();
        });
    }

    public function clearUserCache(Model $notifiable): void
    {
        $cacheKey = "unread_notifications_{$notifiable->getMorphClass()}_{$notifiable->id}";
        Cache::forget($cacheKey);
    }
}
```

### Queue Configuration

```php
// config/queue.php
'connections' => [
    'notifications' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'notifications',
        'retry_after' => 90,
        'block_for' => 5,
    ],
    
    'high_priority' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'high_priority_notifications',
        'retry_after' => 60,
        'block_for' => 2,
    ]
];
```

## 📈 Monitoring və Logging

### Custom Log Channel

```php
// config/logging.php
'channels' => [
    'notifications' => [
        'driver' => 'single',
        'path' => storage_path('logs/notifications.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],
];
```

### Notification Event Listeners

```php
// App\Listeners\NotificationEventListener
class NotificationEventListener
{
    public function handle($event)
    {
        match(get_class($event)) {
            NotificationSent::class => $this->logNotificationSent($event),
            NotificationFailed::class => $this->logNotificationFailed($event),
            NotificationRead::class => $this->logNotificationRead($event),
            default => null
        };
    }

    private function logNotificationSent(NotificationSent $event): void
    {
        Log::channel('notifications')->info('Notification sent', [
            'notification_id' => $event->notification->id,
            'notifiable_type' => $event->notifiable::class,
            'notifiable_id' => $event->notifiable->id,
            'channel' => $event->channel,
            'type' => $event->notification->type
        ]);
    }

    private function logNotificationFailed(NotificationFailed $event): void
    {
        Log::channel('notifications')->error('Notification failed', [
            'notification_id' => $event->notification->id,
            'channel' => $event->channel,
            'error' => $event->error,
            'data' => $event->data
        ]);
    }
}
```

### Health Check

```php
// App\Http\Controllers\HealthController
class HealthController extends Controller
{
    public function notifications(NotificationService $service): JsonResponse
    {
        $health = [
            'status' => 'healthy',
            'checks' => []
        ];

        try {
            // Database əlaqəsi
            $totalNotifications = Notification::count();
            $health['checks']['database'] = [
                'status' => 'ok',
                'total_notifications' => $totalNotifications
            ];

            // Queue status
            $queueSize = Queue::size('notifications');
            $health['checks']['queue'] = [
                'status' => $queueSize < 1000 ? 'ok' : 'warning',
                'queue_size' => $queueSize
            ];

            // Channel status
            foreach (['email', 'push', 'telegram'] as $channel) {
                $recentFailures = NotificationDelivery::where('channel', $channel)
                    ->where('success', false)
                    ->where('delivered_at', '>', now()->subHour())
                    ->count();

                $health['checks'][$channel] = [
                    'status' => $recentFailures < 10 ? 'ok' : 'warning',
                    'recent_failures' => $recentFailures
                ];
            }

        } catch (Exception $e) {
            $health['status'] = 'unhealthy';
            $health['error'] = $e->getMessage();
        }

        return response()->json($health);
    }
}
```

## 📋 TODO və Gələcək Təkmilləşdirmələr

- [ ] **WebSocket Integration** - Real-time notification updates
- [ ] **Advanced Templates** - Rich HTML email templates
- [ ] **A/B Testing** - Notification content testing
- [ ] **Machine Learning** - Optimal send time prediction
- [ ] **Multi-language Support** - Dynamic content localization
- [ ] **User Preferences** - Granular notification settings
- [ ] **Delivery Analytics** - Advanced reporting dashboard
- [ ] **Rate Limiting** - Advanced throttling per user/channel
- [ ] **Notification Categories** - User-defined categories
- [ ] **Batch Processing** - Efficient bulk operations

## ⚠️ Təhlükəsizlik və Best Practices

### Security Considerations

1. **Input Validation** - Bütün user input-lar validate edilir
2. **Authorization** - Notification əməliyyatları icazə sistemi ilə qorunur
3. **Rate Limiting** - Spam və abuse-in qarşısını alır
4. **Data Sanitization** - XSS və injection hücumlarına qarşı qoruma
5. **Access Control** - İstifadəçilər yalnız öz bildirişlərini görə bilir

### Performance Best Practices

1. **Database Indexing** - Bütün axtarış sahələrində indekslər
2. **Caching Strategy** - User statistics və unread counts cache-lənir
3. **Queue Processing** - Ağır əməliyyatlar queue-da işlənir
4. **Batch Operations** - Çoxlu əməliyyatlar batch-larda həyata keçirilir
5. **Connection Pooling** - External API çağırışları optimize edilir

### Monitoring Checklist

- [ ] Notification delivery rates tracked
- [ ] Queue sizes monitored
- [ ] Channel failure rates logged
- [ ] Database performance metrics
- [ ] API response times measured
- [ ] Error rates and patterns analyzed

## 🤝 Töhfə və Support

Bu service-in inkişafında iştirak etmək üçün:
1. Bug report-lar göndərin
2. Feature request-lər təqdim edin
3. Code review-lərində iştirak edin
4. Dokumentasiyanı təkmilləşdirin

---

**Son yenilənmə**: 2025-01-15  
**Versiya**: 1.0.0  
**Laravel versiyası**: 12.x
