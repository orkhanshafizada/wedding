# User Service Documentation

## 📋 Ümumi Məlumat

User Service Laravel 12 layihəsində istifadəçi məlumatlarının idarə edilməsi və analitik dashboard funksiyalarını təmin edir. Bu service CRUD əməliyyatları, analitik hesabatlar, dashboard məlumatları və istifadəçi fəaliyyət analizini dəstəkləyir.

## 🚀 Xüsusiyyətlər

- ✅ **CRUD Əməliyyatları** - Tam istifadəçi idarəetməsi
- ✅ **Dashboard Analytics** - Ətraflı analitik məlumatlar
- ✅ **User Activity Tracking** - İstifadəçi fəaliyyət izləməsi
- ✅ **Favorites Management** - İstifadəçi sevimliləri
- ✅ **Listings Management** - İstifadəçi elanları idarəetməsi
- ✅ **Balance Tracking** - Balans və ödəniş tarixçəsi
- ✅ **Referral Analytics** - Referral sistemi analitikası
- ✅ **Company Gallery** - Şirkət qalereya idarəetməsi
- ✅ **Filtering & Search** - Güclü filtrleme sistemi
- ✅ **Status Management** - İstifadəçi status idarəetməsi

## 🔗 API Endpoints

### Admin Panel Endpoints

| HTTP Method | Endpoint | Açıqlama | Permission |
|-------------|----------|----------|------------|
| `GET` | `/api/admin/users` | İstifadəçi siyahısı (pagination) | `user.read` |
| `POST` | `/api/admin/users` | Yeni istifadəçi yaratma | `user.create` |
| `GET` | `/api/admin/users/{id}` | İstifadəçi ətraflı məlumat | `user.read` |
| `PUT` | `/api/admin/users/{id}` | İstifadəçi məlumat yeniləmə | `user.update` |
| `DELETE` | `/api/admin/users/{id}` | İstifadəçi silmə | `user.delete` |
| `POST` | `/api/admin/users/{id}/action` | Status dəyişikliyi | `user.status` |
| `GET` | `/api/admin/users/dashboard` | Dashboard məlumatları | `user.read` |

### Frontend Endpoints

| HTTP Method | Endpoint | Açıqlama | Auth Required |
|-------------|----------|----------|---------------|
| `GET` | `/api/front/user/favorites` | İstifadəçi sevimliləri | ✅ |
| `POST` | `/api/front/user/favorite-action` | Sevimli əlavə/silmə | ✅ |
| `GET` | `/api/front/user/listings` | İstifadəçi elanları | ✅ |
| `GET` | `/api/front/user/listings-total` | Elan sayı statistikası | ✅ |
| `GET` | `/api/front/user/balance` | Balans məlumatları | ✅ |
| `GET` | `/api/front/user/referrals` | Referral məlumatları | ✅ |
| `GET` | `/api/front/user/payment-histories` | Ödəniş tarixçəsi | ✅ |
| `GET` | `/api/front/user/company-gallery` | Şirkət qalereya | ✅ |
| `POST` | `/api/front/user/company-gallery-upload` | Qalereya yükləmə | ✅ |
| `DELETE` | `/api/front/user/company-gallery-delete` | Qalereya silmə | ✅ |

## 💻 İstifadə Nümunələri

### Admin Panel - İstifadəçi İdarəetməsi

```javascript
// İstifadəçi siyahısını əldə etmə
const getUsers = async (page = 1, filters = {}) => {
    const params = new URLSearchParams({
        page,
        per_page: 15,
        ...filters
    });

    const response = await fetch(`/api/admin/users?${params}`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    });

    const result = await response.json();
    return result;
};

// Yeni istifadəçi yaratma
const createUser = async (userData) => {
    const response = await fetch('/api/admin/users', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            name: userData.name,
            surname: userData.surname,
            email: userData.email,
            password: userData.password,
            role_id: userData.role_id,
            gender: userData.gender
        })
    });

    if (response.ok) {
        const result = await response.json();
        console.log('İstifadəçi yaradıldı:', result);
        return result;
    } else {
        const error = await response.json();
        console.error('Xəta:', error);
        throw error;
    }
};

// İstifadəçi statusunu dəyişmə
const changeUserStatus = async (userId, status) => {
    const response = await fetch(`/api/admin/users/${userId}/action`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status })
    });

    const result = await response.json();
    return result;
};
```

### Frontend - İstifadəçi Dashboard

```javascript
// İstifadəçi sevimliləri
const getFavorites = async () => {
    const response = await fetch('/api/front/user/favorites', {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });

    const result = await response.json();
    return result.data; // ListingResource collection
};

// Sevimli əlavə/silmə
const toggleFavorite = async (listingUuid) => {
    const response = await fetch('/api/front/user/favorite-action', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ uuid: listingUuid })
    });

    const result = await response.json();
    return result;
};

// İstifadəçi elanları (status əsasında)
const getUserListings = async (status = null) => {
    const url = status 
        ? `/api/front/user/listings?status=${status}`
        : '/api/front/user/listings';

    const response = await fetch(url, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });

    const result = await response.json();
    return result;
};

// Balans məlumatları
const getBalance = async () => {
    const response = await fetch('/api/front/user/balance', {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });

    const result = await response.json();
    return result;
};

// Referral məlumatları
const getReferrals = async () => {
    const response = await fetch('/api/front/user/referrals', {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });

    const result = await response.json();
    return result;
};
```

### Company Gallery İdarəetməsi

```javascript
// Qalereya məlumatlarını əldə etmə
const getCompanyGallery = async () => {
    const response = await fetch('/api/front/user/company-gallery', {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });

    const result = await response.json();
    return result;
};

// Qalereya şəkil yükləmə
const uploadGalleryImage = async (base64Image) => {
    const response = await fetch('/api/front/user/company-gallery-upload', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ photo: base64Image })
    });

    const result = await response.json();
    return result;
};

// Qalereya şəkil silmə
const deleteGalleryImage = async (imagePath) => {
    const response = await fetch('/api/front/user/company-gallery-delete', {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ path: imagePath })
    });

    const result = await response.json();
    return result;
};
```

## 🛠 Service Metodları

### UserService Class

```php
namespace App\Services\Module;

class UserService
{
    /**
     * Yeni istifadəçi yaradır
     * 
     * @param array $data İstifadəçi məlumatları
     * @return User
     */
    public function create(array $data): User

    /**
     * İstifadəçi məlumatlarını yeniləyir
     * 
     * @param int $id İstifadəçi ID-si
     * @param array $data Yeni məlumatlar
     * @return Model
     */
    public function update(int $id, array $data): Model

    /**
     * İstifadəçi statusunu dəyişir
     * 
     * @param int $id İstifadəçi ID-si
     * @param array $request Status məlumatları
     * @return Model
     */
    public function changeStatus(int $id, array $request): Model

    /**
     * ID ilə istifadəçi tapır
     * 
     * @param int $id İstifadəçi ID-si
     * @return Model
     */
    public function findById(int $id): Model

    /**
     * Pagination və filtrleme ilə istifadəçi siyahısı
     * 
     * @return LengthAwarePaginator
     */
    public function paginateAndFilter(): LengthAwarePaginator

    /**
     * Aktiv istifadəçi siyahısı
     * 
     * @return Collection
     */
    public function findActiveList(): Collection

    /**
     * İstifadəçi silmə
     * 
     * @param int $id İstifadəçi ID-si
     * @return bool
     */
    public function delete(int $id): bool

    /**
     * Filter optionları
     * 
     * @return array
     */
    public function filters(): array

    /**
     * Dashboard məlumatları
     * 
     * @return array
     */
    public function getDashboardData(): array
}
```

## 📊 Dashboard Analytics

### Dashboard Məlumat Strukturu

```php
// getDashboardData() metodunun qaytardığı struktur
[
    'stats' => [
        'total_users' => 1250,
        'active_users' => 1100, 
        'new_users_24h' => 15,
        'unverified_emails' => 45,
        'social_login_users' => 320
    ],
    'recent_activity' => [
        'new_users' => [...], // Son 5 yeni istifadəçi
        'last_logins' => [...] // Son 5 giriş
    ],
    'referral_stats' => [
        'total_earnings' => 15420.50,
        'active_codes' => 89,
        'last_7_days_referrals' => 12
    ],
    'activity_logs' => [...], // Son 5 fəaliyyət logu
    'distributions' => [
        'status' => [...], // Status paylanması
        'gender' => [...] // Gender paylanması
    ],
    'balances' => [
        'total_main_balance' => 45620.75,
        'total_referral_balance' => 8940.25,
        'top_users' => [...] // Ən yüksək balansı olanlar
    ],
    'login_trends' => [
        'last_7_days' => [...], // Son 7 günün giriş trendləri
        'device_types' => [...] // Cihaz növləri paylanması
    ],
    'preferences' => [
        'dark_mode_users' => 680,
        'light_mode_users' => 570,
        'top_languages' => [...] // Ən çox istifadə olunan dillər
    ]
]
```

### Dashboard JavaScript İstifadəsi

```javascript
// Admin dashboard məlumatlarını əldə etmə
const getDashboardData = async () => {
    try {
        const response = await fetch('/api/admin/users/dashboard', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const dashboardData = await response.json();
            
            // Statistik kartlar üçün
            updateStatsCards(dashboardData.stats);
            
            // Chartlar üçün
            updateLoginTrends(dashboardData.login_trends);
            updateDistributionCharts(dashboardData.distributions);
            
            // Son fəaliyyət üçün
            updateRecentActivity(dashboardData.recent_activity);
            
            return dashboardData;
        }
    } catch (error) {
        console.error('Dashboard məlumatları alınmadı:', error);
    }
};

// Statistik kartları yeniləmə
const updateStatsCards = (stats) => {
    document.getElementById('total-users').textContent = stats.total_users;
    document.getElementById('active-users').textContent = stats.active_users;
    document.getElementById('new-users-24h').textContent = stats.new_users_24h;
    document.getElementById('unverified-emails').textContent = stats.unverified_emails;
};

// Chart.js ilə login trendlərini göstərmə
const updateLoginTrends = (loginTrends) => {
    const ctx = document.getElementById('loginTrendsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: loginTrends.last_7_days.map(item => item.date),
            datasets: [{
                label: 'Giriş sayı',
                data: loginTrends.last_7_days.map(item => item.count),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Son 7 günün giriş trendləri'
                }
            }
        }
    });
};
```

## 🏗️ UserRepository Metodları

UserService UserRepository-dən istifadə edir. Əsas repository metodları:

### Statistik Metodlar

```php
// İstifadəçi sayma metodları
public function countTotalUsers(): int
public function countActiveUsers(): int  
public function countNewUsersLast24Hours(): int
public function countUnverifiedEmails(): int
public function countSocialLoginUsers(): int

// Referral metodları  
public function getTotalReferralEarnings(): float
public function countActiveReferralCodes(): int
public function countReferralsLast7Days(): int

// Balans metodları
public function getTotalMainBalance(): float
public function getTotalReferralBalance(): float
public function getTopUsersByBalance(int $limit): Collection

// Preferences metodları
public function countDarkModeUsers(): int
public function countLightModeUsers(): int
public function getTopLanguages(int $limit): Collection
```

### Activity və Trend Metodları

```php
// Son fəaliyyətlər
public function getRecentUsers(int $limit): Collection
public function getRecentLogins(int $limit): Collection  
public function getRecentActivityLogs(int $limit): array

// Trendlər və paylanma
public function getLoginTrendLast7Days(): array
public function getDeviceTypeDistribution(): array
public function getStatusDistribution(): array
public function getGenderDistribution(): array
```

### Frontend Xüsusi Metodları

```php
// Sevimli idarəetməsi
public function fetchFavorites(): Collection
public function favoriteAction(string $uuid): array

// Elan idarəetməsi  
public function fetchListingsByStatus(?string $status): LengthAwarePaginator

// Balans və ödəniş
public function fetchBalance(): array
public function fetchPaymentHistories(): LengthAwarePaginator

// Referral sistemi
public function fetchReferral(): array

// Company gallery
public function fetchCompanyGalleries(): array
public function fetchCompanyGalleryUpload(string $base64Image): array
public function fetchCompanyGalleryDelete(string $path): bool
```

## 🔍 Filtrleme Sistemi

### Filter Options

```php
// UserService filters() metodu
public function filters(): array
{
    return [
        'permissions' => Role::query()->get(), // Bütün rollar
        'genders' => [
            ['id' => 'male', 'name' => 'Kişi'],
            ['id' => 'female', 'name' => 'Qadın']
        ],
        'statuses' => [
            ['id' => 'active', 'name' => 'Aktiv'],
            ['id' => 'inactive', 'name' => 'Deaktiv'],
            ['id' => 'pending_mail', 'name' => 'Email gözləyən'],
            ['id' => 'block', 'name' => 'Bloklanmış']
        ]
    ];
}
```

### Frontend Filter İstifadəsi

```javascript
// Filter optionlarını əldə etmə
const getFilterOptions = async () => {
    const response = await fetch('/api/admin/users/filters', {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    
    const filters = await response.json();
    return filters;
};

// Filterləməli axtarış
const searchUsers = async (filters) => {
    const params = new URLSearchParams();
    
    if (filters.search) params.append('search', filters.search);
    if (filters.status) params.append('status', filters.status);
    if (filters.gender) params.append('gender', filters.gender);
    if (filters.role_id) params.append('role_id', filters.role_id);
    if (filters.date_from) params.append('date_from', filters.date_from);
    if (filters.date_to) params.append('date_to', filters.date_to);
    
    const response = await fetch(`/api/admin/users?${params}`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    
    return await response.json();
};
```

## 📝 Validation Rules

### Admin Panel Validation

```php
// UserController commonRules() metodu
public function commonRules(): array
{
    return [
        'name' => ['required'],
        'surname' => ['required'], 
        'email' => ['required', 'email', Rule::unique('users', 'email')->ignore(request()->id)],
        'password' => request()->id ? ['sometimes'] : ['required'],
        'role_id' => ['required', 'exists:roles,id'],
        'gender' => ['required', 'in:male,female'],
    ];
}
```

### Frontend Validation

```javascript
// JavaScript validation nümunələri
const validateUserData = (userData) => {
    const errors = {};
    
    if (!userData.name?.trim()) {
        errors.name = 'Ad sahəsi tələb olunur';
    }
    
    if (!userData.surname?.trim()) {
        errors.surname = 'Soyad sahəsi tələb olunur';
    }
    
    if (!userData.email?.trim()) {
        errors.email = 'Email sahəsi tələb olunur';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(userData.email)) {
        errors.email = 'Email formatı düzgün deyil';
    }
    
    if (!userData.role_id) {
        errors.role_id = 'Rol seçimi tələb olunur';
    }
    
    if (!userData.gender) {
        errors.gender = 'Cins seçimi tələb olunur';
    }
    
    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
};

// Company gallery validation
const validateGalleryImage = (base64Image) => {
    if (!base64Image) {
        return { isValid: false, error: 'Şəkil tələb olunur' };
    }
    
    // Base64 format yoxlaması
    if (!base64Image.startsWith('data:image/')) {
        return { isValid: false, error: 'Yalnız şəkil faylları qəbul edilir' };
    }
    
    // Ölçü yoxlaması (məsələn, 5MB)
    const sizeInBytes = (base64Image.length * 3) / 4;
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    if (sizeInBytes > maxSize) {
        return { isValid: false, error: 'Şəkil ölçüsü 5MB-dan böyük ola bilməz' };
    }
    
    return { isValid: true };
};
```

## 🔐 Authorization və Permissions

### Admin Panel Permissions

```php
// Controller-də permission yoxlaması
if (!$this->authorizeAction('read')) {
    return response()->json(['message' => $this->forbiddenMessage], 403);
}

// Permission siyahısı:
// - user.read    : İstifadəçi məlumatlarını oxuma
// - user.create  : Yeni istifadəçi yaratma  
// - user.update  : İstifadəçi məlumat yeniləmə
// - user.delete  : İstifadəçi silmə
// - user.status  : Status dəyişikliyi
```

### Frontend Authentication

```javascript
// Token yoxlaması
const checkAuthToken = () => {
    const token = localStorage.getItem('auth_token');
    if (!token) {
        window.location.href = '/login';
        return false;
    }
    return token;
};

// API response error handling
const handleApiResponse = async (response) => {
    if (response.status === 401) {
        // Token expired
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');
        window.location.href = '/login';
        return null;
    }
    
    if (response.status === 403) {
        // Permission denied
        console.error('Bu əməliyyat üçün icazəniz yoxdur');
        return null;
    }
    
    return await response.json();
};
```

## 🚀 Performance Optimizasiyası

### Cache İstifadəsi

```php
// UserRepository-də cache metodları
protected bool $useCache = true;

// Cache key generation
protected function getCacheKey(string $method, array $params = []): string
{
    return sprintf(
        '%s_%s_%s',
        $this->model->getTable(),
        $method,
        md5(serialize($params))
    );
}

// Cache remember pattern
public function findActiveList(): Collection
{
    $cacheKey = $this->getCacheKey('findActiveList');
    
    return $this->remember($cacheKey, function () {
        return $this->model->query()
            ->where('status', UserStatusEnum::Active)
            ->where('is_system', false)
            ->get();
    });
}
```

### Database Optimizasiyası

```sql
-- İndekslər performance üçün
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_login_history_user_logged_in ON user_login_history(user_id, logged_in_at);
```

## 📱 Frontend Components

### React/Vue Component nümunələri

```jsx
// UserDashboard.jsx
import React, { useState, useEffect } from 'react';

const UserDashboard = () => {
    const [dashboardData, setDashboardData] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchDashboardData();
    }, []);

    const fetchDashboardData = async () => {
        try {
            const response = await fetch('/api/admin/users/dashboard', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                setDashboardData(data);
            }
        } catch (error) {
            console.error('Dashboard məlumatları alınmadı:', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <div>Yüklənir...</div>;

    return (
        <div className="dashboard">
            <div className="stats-grid">
                <div className="stat-card">
                    <h3>Ümumi İstifadəçi</h3>
                    <p>{dashboardData.stats.total_users}</p>
                </div>
                <div className="stat-card">
                    <h3>Aktiv İstifadəçi</h3>
                    <p>{dashboardData.stats.active_users}</p>
                </div>
                <div className="stat-card">
                    <h3>Yeni İstifadəçi (24s)</h3>
                    <p>{dashboardData.stats.new_users_24h}</p>
                </div>
            </div>
            
            {/* Charts və digər komponentlər */}
        </div>
    );
};

export default UserDashboard;
```

## 🧪 Testing

### Unit Tests

```php
// UserServiceTest.php
class UserServiceTest extends TestCase
{
    public function test_can_create_user()
    {
        $userData = [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role_id' => 1,
            'gender' => 'male'
        ];

        $user = $this->userService->create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_dashboard_data_structure()
    {
        $dashboardData = $this->userService->getDashboardData();

        $this->assertArrayHasKey('stats', $dashboardData);
        $this->assertArrayHasKey('recent_activity', $dashboardData);
        $this->assertArrayHasKey('referral_stats', $dashboardData);
        
        $this->assertIsInt($dashboardData['stats']['total_users']);
        $this->assertIsInt($dashboardData['stats']['active_users']);
    }
}
```

### API Testing

```javascript
// Jest test nümunəsi
describe('User API Tests', () => {
    test('should fetch user dashboard data', async () => {
        const mockToken = 'mock-jwt-token';
        
        fetch.mockResolvedValueOnce({
            ok: true,
            json: () => Promise.resolve({
                stats: {
                    total_users: 100,
                    active_users: 80
                }
            })
        });

        const dashboardData = await getDashboardData();
        
        expect(dashboardData.stats.total_users).toBe(100);
        expect(dashboardData.stats.active_users).toBe(80);
    });

    test('should handle favorite toggle', async () => {
        fetch.mockResolvedValueOnce({
            ok: true,
            json: () => Promise.resolve({
                status: 'added',
                message: 'Sevimli əlavə edildi'
            })
        });

        const result = await toggleFavorite('listing-uuid-123');
        
        expect(result.status).toBe('added');
    });
});
```

## 📋 TODO və Gələcək Təkmilləşdirmələr

- [ ] **Real-time notifications** WebSocket dəstəyi
- [ ] **Advanced analytics** dashboard
- [ ] **User behavior tracking** AI əsaslı analiz
- [ ] **Bulk operations** çoxlu istifadəçi əməliyyatları
- [ ] **Export functionality** Excel/PDF export
- [ ] **Advanced search** Elasticsearch integration
- [ ] **User segmentation** marketing purposes
- [ ] **A/B testing** framework integration

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
