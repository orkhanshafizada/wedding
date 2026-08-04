# Authorization Service Documentation

## 📋 Ümumi Məlumat

Authorization Service Laravel 12 layihəsində istifadəçi autentifikasiyası və avtorizasiya proseslərini idarə edir. Bu service tam təhlükəsizlik tədbirləri, activity logging və sosial media inteqrasiyası dəstəyi ilə hazırlanmışdır.

## 🚀 Xüsusiyyətlər

- ✅ **İstifadəçi Qeydiyyatı** - Tam təhlükəsizlik yoxlamaları ilə
- ✅ **Giriş/Çıxış Sistemi** - IP və email bloklaması dəstəyi
- ✅ **Şifrə Berpası** - Token əsaslı təhlükəsiz sistem
- ✅ **Email Təsdiqi** - Avtomatik təsdiq sistemi
- ✅ **Sosial Media Girişi** - Google, Facebook və s. dəstəyi
- ✅ **Giriş Tarixçəsi** - Tam audit trail və izləmə
- ✅ **Referral Sistemi** - Daxili referral bonusları
- ✅ **İstifadəçi Preferences** - Şəxsi tənzimləmələr
- ✅ **Activity Logging** - Bütün əməliyyatların logi
- ✅ **Cache Dəstəyi** - Performance optimizasiyası

## 📊 Məlumat Bazası Strukturu

### `users` cədvəli
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NULL,
    surname VARCHAR(255) NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    provider VARCHAR(255) NULL,
    provider_id VARCHAR(255) NULL,
    main_balance DECIMAL(10,2) DEFAULT 0,
    referral_balance DECIMAL(10,2) DEFAULT 0,
    referral_code VARCHAR(255) NULL UNIQUE,
    gender ENUM('male','female','other') NULL,
    phone VARCHAR(255) NULL,
    email_verified_at TIMESTAMP NULL,
    status VARCHAR(255) DEFAULT 'pending_mail',
    social_links JSON NULL,
    is_system BOOLEAN DEFAULT FALSE,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);
```

### `user_login_history` cədvəli
```sql
CREATE TABLE user_login_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    ip_address VARCHAR(255) NOT NULL,
    user_agent VARCHAR(255) NOT NULL,
    location VARCHAR(255) NULL,
    device_type VARCHAR(255) NULL,
    meta_data JSON NULL,
    logged_in_at TIMESTAMP NOT NULL,
    logged_out_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### `user_preferences` cədvəli
```sql
CREATE TABLE user_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE NOT NULL,
    dark_mode BOOLEAN DEFAULT FALSE,
    language VARCHAR(255) DEFAULT 'az',
    notification_settings JSON NULL,
    content_preferences JSON NULL,
    timezone VARCHAR(255) DEFAULT 'UTC',
    email_frequency ENUM('daily','weekly','monthly','never') DEFAULT 'weekly',
    show_email BOOLEAN DEFAULT FALSE,
    show_profile_views BOOLEAN DEFAULT TRUE,
    privacy_settings JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### `user_social_logins` cədvəli
```sql
CREATE TABLE user_social_logins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(255) NOT NULL,
    provider_id VARCHAR(255) NOT NULL,
    provider_data JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## 🔗 API Endpoints

### Authentication Routes

| HTTP Method | Endpoint | Açıqlama | Middleware |
|-------------|----------|----------|------------|
| `POST` | `/api/auth/register` | İstifadəçi qeydiyyatı | `check.blocked` |
| `POST` | `/api/auth/login` | İstifadəçi girişi | `check.blocked` |
| `POST` | `/api/auth/logout` | İstifadəçi çıxışı | `auth:sanctum` |
| `GET` | `/api/auth/user` | Cari istifadəçi məlumatları | `auth:sanctum` |
| `POST` | `/api/auth/refresh` | Token yeniləmə | `auth:sanctum` |
| `POST` | `/api/auth/forgot-password` | Şifrə berpası tələbi | `check.blocked` |
| `POST` | `/api/auth/reset-password` | Şifrə yeniləmə | - |
| `POST` | `/api/auth/email/verify` | Email təsdiqi | - |
| `POST` | `/api/auth/email/resend` | Təsdiq emaili yenidən göndərmə | `auth:sanctum` |

### Social Authentication Routes

| HTTP Method | Endpoint | Açıqlama | Middleware |
|-------------|----------|----------|------------|
| `POST` | `/api/auth/social/redirect` | Sosial giriş yönləndirməsi | `check.blocked` |
| `POST` | `/api/auth/social/callback` | Sosial giriş callback | `check.blocked` |

## 💻 İstifadə Nümunələri

### İstifadəçi Qeydiyyatı

```javascript
const registerUser = async () => {
    const registerData = {
        name: "Adı",
        surname: "Soyadı",
        email: "user@example.com",
        password: "güclüŞifrə123!",
        password_confirmation: "güclüŞifrə123!"
    };

    try {
        const response = await fetch('/api/auth/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(registerData)
        });

        const result = await response.json();
        
        if (response.ok) {
            console.log('Qeydiyyat uğurlu:', result.message);
        } else {
            console.error('Qeydiyyat xətası:', result);
        }
    } catch (error) {
        console.error('Network xətası:', error);
    }
};
```

### İstifadəçi Girişi

```javascript
const loginUser = async () => {
    const loginData = {
        email: "user@example.com",
        password: "güclüŞifrə123!"
    };

    try {
        const response = await fetch('/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(loginData)
        });

        const result = await response.json();
        
        if (response.ok) {
            const { token, user, message } = result;
            
            // Token-i localStorage-də saxla
            localStorage.setItem('auth_token', token);
            localStorage.setItem('user_data', JSON.stringify(user));
            
            console.log('Giriş uğurlu:', message);
            return { token, user };
        } else {
            console.error('Giriş xətası:', result);
        }
    } catch (error) {
        console.error('Network xətası:', error);
    }
};
```

### Authenticated Request-lər

```javascript
const makeAuthenticatedRequest = async (url, options = {}) => {
    const token = localStorage.getItem('auth_token');
    
    const headers = {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers
    };

    try {
        const response = await fetch(url, {
            ...options,
            headers
        });

        if (response.status === 401) {
            // Token-in müddəti bitib, yenidən login et
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_data');
            window.location.href = '/login';
            return;
        }

        return await response.json();
    } catch (error) {
        console.error('Request xətası:', error);
        throw error;
    }
};

// İstifadə nümunəsi
const getCurrentUser = async () => {
    return await makeAuthenticatedRequest('/api/auth/user');
};

const logoutUser = async () => {
    const result = await makeAuthenticatedRequest('/api/auth/logout', {
        method: 'POST'
    });
    
    // Local storage-i təmizlə
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_data');
    
    return result;
};
```

### Şifrə Berpası

```javascript
const forgotPassword = async (email) => {
    try {
        const response = await fetch('/api/auth/forgot-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email })
        });

        const result = await response.json();
        
        if (response.ok) {
            console.log('Şifrə berpası linki göndərildi:', result.message);
        } else {
            console.error('Xəta:', result);
        }
    } catch (error) {
        console.error('Network xətası:', error);
    }
};

const resetPassword = async (token, password, passwordConfirmation) => {
    try {
        const response = await fetch('/api/auth/reset-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                token,
                password,
                password_confirmation: passwordConfirmation
            })
        });

        const result = await response.json();
        
        if (response.ok) {
            const { token: newToken, user, message } = result;
            
            // Yeni token-i saxla
            localStorage.setItem('auth_token', newToken);
            localStorage.setItem('user_data', JSON.stringify(user));
            
            console.log('Şifrə uğurla dəyişdirildi:', message);
            return { token: newToken, user };
        } else {
            console.error('Şifrə dəyişmə xətası:', result);
        }
    } catch (error) {
        console.error('Network xətası:', error);
    }
};
```

## 🛠 Service Metodları

### AuthService Class

```php
namespace App\Services\Module;

class AuthService
{
    /**
     * İstifadəçi qeydiyyatı prosesini həyata keçirir
     * 
     * @param array $data İstifadəçi məlumatları
     * @throws BaseException
     */
    public function register(array $data): void

    /**
     * İstifadəçi login əməliyyatını həyata keçirir
     * 
     * @param array $credentials İstifadəçi məlumatları
     * @return array Token və istifadəçi məlumatları
     * @throws BaseException
     */
    public function login(array $credentials): array

    /**
     * İstifadəçini sistemdən çıxarır
     * 
     * @param User $user
     */
    public function logout(User $user): void

    /**
     * İstifadəçi token-ini yeniləyir
     * 
     * @param User $user
     * @return string Yeni token
     */
    public function refreshToken(User $user): string

    /**
     * Şifrə berpası linki göndərir
     * 
     * @param array $data Email məlumatları
     * @throws BaseException
     */
    public function sendResetLinkEmail(array $data): void

    /**
     * İstifadəçinin şifrəsini yeniləyir
     * 
     * @param array $data Şifrə yeniləmə məlumatları
     * @return array Token və istifadəçi məlumatları
     * @throws BaseException
     */
    public function resetPassword(array $data): array

    /**
     * İstifadəçinin email ünvanını təsdiqləyir
     * 
     * @param Request $request
     * @throws BaseException
     */
    public function verifyEmail(Request $request): void

    /**
     * Email təsdiqi məktubunu yenidən göndərir
     * 
     * @param User $user
     * @throws BaseException
     */
    public function resendVerificationEmail(User $user): void
}
```

## 🔐 Təhlükəsizlik Xüsusiyyətləri

### Bloklanma Sistemi

Service avtomatik olaraq şübhəli fəaliyyətləri aşkar edir və müvafiq tədbirlər görür:

#### IP Bloklaması
- Çox sayda uğursuz giriş cəhdi
- Spam qeydiyyat cəhdləri
- Müəyyən müddətə avtomatik bloklanma

#### Email Bloklaması
- Sui-istifadə edən email ünvanları
- Spam fəaliyyət göstərən hesablar

### Şifrə Siyasəti

Sistemdə güclü şifrə siyasəti tətbiq edilir:

```env
# .env faylında konfiqurasiya
SECURITY_PASSWORD_MIN_LENGTH=8
SECURITY_REQUIRE_UPPERCASE=true
SECURITY_REQUIRE_NUMERIC=true
SECURITY_REQUIRE_SPECIAL_CHARS=true
SECURITY_MAX_LOGIN_ATTEMPTS=5
SECURITY_LOGIN_LOCKOUT_TIME=15
```

**Şifrə tələbləri:**
- Minimum 8 simvol
- Ən azı 1 böyük hərf
- Ən azı 1 rəqəm
- Ən azı 1 xüsusi simvol

### Rate Limiting

Giriş cəhdləri üçün limit sistemi:
- Maksimum 5 uğursuz cəhd
- 15 dəqiqə bloklanma müddəti
- IP və email əsaslı takip

## 📝 İstifadəçi Statusları

```php
use App\Enums\UserStatusEnum;

UserStatusEnum::Active           // Aktiv istifadəçi - bütün funksiyalara çıxış
UserStatusEnum::Inactive         // Deaktiv istifadəçi - giriş qadağan
UserStatusEnum::PendingMail      // Email təsdiqi gözləyən - məhdud çıxış
UserStatusEnum::PendingProfile   // Profil tamamlama gözləyən - məhdud çıxış
UserStatusEnum::Block            // Bloklanmış istifadəçi - tam qadağa
```

## 👤 User Model Xüsusiyyətləri

### Relationships

```php
// Əsas əlaqələr
$user->preferences()      // İstifadəçi tənzimləmələri
$user->socialLogins()     // Sosial giriş hesabları
$user->loginHistory()     // Giriş tarixçəsi
$user->role()             // İstifadəçi rolu

// Referral sistemi
$user->referrals()        // İstifadəçinin referralları
$user->referredBy()       // Kim tərəfindən refer edilib

// Digər əlaqələr
$user->company()          // İstifadəçinin şirkəti
$user->listings()         // İstifadəçinin elanları
$user->payments()         // İstifadəçinin ödənişləri
```

### Helper Metodları

#### Permission və Role Yoxlamaları
```php
// Permission yoxlaması
$user->hasPermission('user.create')
$user->getAllPermissions()
$user->forgetCachedPermissions()

// Role yoxlaması
$user->hasRole('admin')
```

#### Referral Sistemi
```php
// Referral kodu yaratma
$user->generateReferralCode()

// Referral linki əldə etmə
$referralLink = $user->getReferralLink()

// Bonus əlavə etmə
$user->addReferralBonus(100.00, 'Yeni referral bonusu')

// Statistikalar
$stats = $user->getReferralStats()
```

#### Status və Yoxlamalar
```php
// Email təsdiqi
$user->hasVerifiedEmail()

// Şirkət yoxlaması
$user->hasCompany()

// Bloklanma yoxlamaları
$user->hasBlocked($userId)
$user->isBlockedBy($userId)
```

## 🛡️ Middleware-lər

### check.blocked Middleware

Bu middleware hər request-də bloklanmış IP və email ünvanlarını yoxlayır:

```php
// Route-larda istifadə
Route::post('login', 'login')->middleware('check.blocked');
Route::post('register', 'register')->middleware('check.blocked');
Route::post('forgot-password', 'forgotPassword')->middleware('check.blocked');
```

**Funksiyaları:**
- IP ünvan bloklanma yoxlaması
- Email ünvan bloklanma yoxlaması
- Bloklanma müddəti hesablanması
- Avtomatik blok götürmə

## ⚠️ Error Handling

Service-də strukturlaşdırılmış xəta idarəetməsi:

```php
use App\Exceptions\BaseException;

try {
    $result = $authService->login($credentials);
} catch (BaseException $e) {
    return response()->json([
        'message' => $e->getMessage(),
        'errors' => $e->getErrors(),
        'code' => $e->getCode()
    ], $e->getCode());
} catch (Exception $e) {
    return response()->json([
        'message' => 'Server xətası baş verdi',
        'error' => $e->getMessage()
    ], 500);
}
```

### Xəta Növləri

| Xəta Kodu | Açıqlama |
|-----------|----------|
| `422` | Validation xətaları |
| `401` | Authentication xətaları |
| `403` | Authorization xətaları |
| `429` | Rate limit aşımı |
| `500` | Server xətaları |

## 🚀 Performance Optimizasiyası

### Cache İstifadəsi

```php
// Login cəhdləri cache-də saxlanılır
Cache::put("login_attempts_{$email}_{$ip}", $attempts, now()->addMinutes(15));

// İstifadəçi permissions cache-lənir
Cache::remember("user_permissions_{$userId}", now()->addHours(24), function() {
    return $user->role->permissions;
});

// Blok məlumatları dinamik cache edilir
Cache::remember("blocked_credential_{$type}_{$value}", now()->addMinutes(30), function() {
    return BlockedCredential::where('type', $type)->where('value', $value)->first();
});
```

### Database Optimizasiyası

- **Indexes**: Email, IP ünvan və tarix sahələrində
- **Soft Deletes**: İstifadəçi məlumatları üçün
- **JSON sahələr**: Metadata və preferences üçün
- **Foreign Key Constraints**: Referential integrity

## 📧 Email Templates

### WelcomeEmailMail
Qeydiyyat zamanı göndərilən təbrik emaili:
- Email təsdiq linki
- Xoş gəldin mesajı
- Platform haqqında məlumat

### PasswordResetMail
Şifrə berpası üçün göndərilən email:
- Təhlükəsiz reset linki
- Müddət məhdudiyyəti məlumatı
- Təhlükəsizlik xəbərdarlığı

```php
// Email göndərmə
$this->sendEmail($user, 'welcome');
$this->sendEmail($user, 'password-reset', ['token' => $token]);
```

## 📊 Activity Logging

Bütün autentifikasiya əməliyyatları avtomatik loglanır:

### Log Növləri
- `REGISTER` - Yeni qeydiyyat
- `LOGIN_SUCCESS` - Uğurlu giriş
- `LOGIN_FAILED` - Uğursuz giriş
- `LOGIN_BLOCKED` - Bloklanma səbəbilə giriş qadağası
- `LOGOUT` - İstifadəçi çıxışı
- `PASSWORD_RESET_*` - Şifrə berpası prosesləri
- `EMAIL_VERIFY_*` - Email təsdiq prosesləri

### Log Məlumatları
```php
// Activity log strukturu
[
    'action' => 'LOGIN_SUCCESS',
    'user_id' => 123,
    'ip_address' => '192.168.1.1',
    'user_agent' => 'Mozilla/5.0...',
    'meta_data' => [
        'email' => 'user@example.com',
        'location' => 'Baku, Azerbaijan',
        'device_type' => 'Desktop'
    ],
    'created_at' => '2025-01-15 10:30:00'
]
```

## 🔧 Development və Testing

### Unit Testing
```bash
# AuthService testləri
php artisan test tests/Unit/AuthServiceTest.php

# Feature testləri
php artisan test tests/Feature/AuthTest.php
```

### Debug və Monitoring
```bash
# Laravel Telescope ilə izləmə
php artisan telescope:install

# Log faylları
tail -f storage/logs/laravel.log
```

## 📋 TODO və Gələcək Təkmilləşdirmələr

- [ ] **2FA (Two-Factor Authentication)** dəstəyi
- [ ] **OAuth 2.0** server funksiyası
- [ ] **Biometric authentication** dəstəyi
- [ ] **Advanced fraud detection** sistemi
- [ ] **Machine learning** əsaslı risk analizi
- [ ] **WebSocket** real-time notifications
- [ ] **API versioning** sistemi

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
