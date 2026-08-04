# Translation Management Service Documentation

## 📋 Ümumi Məlumat

Translation Management Service Laravel 12 layihəsində çoxdilli (multi-language) dəstəyi təmin edir. Bu service dil idarəetməsi, tərcümə sistemi, cache optimizasiyası və JSON fayl əsaslı seeding sistemini əhatə edir.

## 🚀 Xüsusiyyətlər

- ✅ **Multi-Language Support** - Çoxlu dil dəstəyi
- ✅ **Dynamic Translation** - Runtime tərcümə yeniləmə
- ✅ **JSON File Integration** - JSON fayllardan avtomatik yükləmə
- ✅ **Caching System** - Yüksək performans üçün cache
- ✅ **Admin Panel** - Tam tərcümə idarəetməsi
- ✅ **Seeding System** - Avtomatik tərcümə seed-i
- ✅ **Default Language** - Default dil sistemi
- ✅ **Nested Translation Keys** - Hierarxik tərcümə açarları
- ✅ **API Integration** - RESTful API dəstəyi
- ✅ **Helper Functions** - Asan istifadə üçün helper-lər

## 📊 Məlumat Bazası Strukturu

### `languages` cədvəli
```sql
CREATE TABLE languages (
                           id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                           name VARCHAR(255) NOT NULL,               -- Dilin adı (Azərbaycan, English)
                           locale VARCHAR(5) UNIQUE NOT NULL,        -- Dil kodu (az, en, ru)
                           is_active BOOLEAN DEFAULT TRUE,           -- Aktiv/deaktiv
                           is_default BOOLEAN DEFAULT FALSE,         -- Default dil
                           created_at TIMESTAMP NULL,
                           updated_at TIMESTAMP NULL,
                           deleted_at TIMESTAMP NULL,

                           INDEX idx_is_active (is_active),
                           INDEX idx_is_default (is_default),
                           INDEX idx_locale (locale)
);
```

### `translates` cədvəli
```sql
CREATE TABLE translates (
                            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                            key VARCHAR(255) NOT NULL,                -- Translation key
                            value LONGTEXT NOT NULL,                  -- Translation value
                            locale VARCHAR(5) NOT NULL,               -- Dil kodu
                            is_system BOOLEAN DEFAULT FALSE,          -- System translation
                            created_at TIMESTAMP NULL,
                            updated_at TIMESTAMP NULL,
                            deleted_at TIMESTAMP NULL,

                            INDEX idx_locale (locale),
                            INDEX idx_key (key),
                            INDEX idx_is_system (is_system),
                            UNIQUE KEY unique_key_locale (key, locale)
);
```

## 🎯 Supported Languages

### System Languages (Default)
```php
$configuredLanguages = [
    ['name' => 'Azərbaycan', 'locale' => 'az', 'is_default' => true],
    ['name' => 'English', 'locale' => 'en'],
    ['name' => 'Русский', 'locale' => 'ru']
];

// Əlavə dillər (aktivləşdirilə bilər)
// ['name' => 'Türkçe', 'locale' => 'tr'],
// ['name' => 'العربية', 'locale' => 'ar'], 
// ['name' => 'Français', 'locale' => 'fr'],
// ['name' => '日本語', 'locale' => 'ja'],
// ['name' => 'Magyar', 'locale' => 'hu']
```

### Translation Key Structure

Translation açarları hierarxik strukturda təşkil edilir:

```json
{
    "enums": {
        "user_status": {
            "active": "Aktiv",
            "inactive": "Aktiv deyil"
        },
        "notification_types": {
            "welcome": "Xoş gəlmisiniz",
            "system_alert": "Sistem xəbərdarlığı"
        }
    },
    "notification": {
        "user_not_found": "İstifadəçi tapılmadı",
        "login_success": "Uğurla giriş etdiniz"
    },
    "validation": {
        "password": {
            "min_length": "Şifrə minimum :length simvol olmalıdır"
        }
    }
}
```

## 🔗 API Endpoints

### Language Management

| HTTP Method | Endpoint | Açıqlama | Permission |
|-------------|----------|----------|------------|
| `GET` | `/api/admin/languages` | Dil siyahısı | `language_read` |
| `POST` | `/api/admin/languages` | Yeni dil əlavə etmə | `language_create` |
| `GET` | `/api/admin/languages/{id}` | Dil ətraflı məlumat | `language_read` |
| `PUT` | `/api/admin/languages/{id}` | Dil məlumat yeniləmə | `language_update` |
| `DELETE` | `/api/admin/languages/{id}` | Dil silmə | `language_delete` |
| `POST` | `/api/admin/languages/{id}/action` | Status dəyişikliyi | `language_status` |
| `GET` | `/api/admin/languages/{locale}/translations` | Dilin tərcümələri | `language_read` |
| `GET` | `/api/admin/languages/current` | Cari dil məlumatı | `language_read` |

### Translation Management

| HTTP Method | Endpoint | Açıqlama | Permission |
|-------------|----------|----------|------------|
| `GET` | `/api/admin/translations` | Tərcümə siyahısı | `translation_read` |
| `POST` | `/api/admin/translations/save` | Tərcümə saxlamaq | `translation_create`/`translation_update` |
| `DELETE` | `/api/admin/translations/{key}` | Tərcümə silmək | `translation_delete` |
| `GET` | `/api/admin/translations/filters` | Filter optionları | `translation_read` |

### Public Endpoints

| HTTP Method | Endpoint | Açıqlama |
|-------------|----------|----------|
| `GET` | `/api/languages` | Aktiv dillər |
| `GET` | `/api/translations/{locale}` | Dilin bütün tərcümələri |

## 💻 İstifadə Nümunələri

### Language Management

```javascript
// Dil siyahısını əldə etmə
const getLanguages = async () => {
    const response = await fetch('/api/admin/languages', {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });

    const languages = await response.json();
    return languages;
};

// Yeni dil əlavə etmə
const createLanguage = async (languageData) => {
    const response = await fetch('/api/admin/languages', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            name: languageData.name,
            locale: languageData.locale,
            is_active: true,
            is_default: false
        })
    });

    return await response.json();
};

// Dil statusunu dəyişmə
const toggleLanguageStatus = async (languageId, isActive) => {
    const response = await fetch(`/api/admin/languages/${languageId}/action`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ is_active: isActive })
    });

    return await response.json();
};
```

### Translation Management

```javascript
// Tərcümələri əldə etmə
const getTranslations = async (page = 1, filters = {}) => {
    const params = new URLSearchParams({
        page,
        per_page: 20,
        ...filters
    });

    const response = await fetch(`/api/admin/translations?${params}`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });

    return await response.json();
};

// Tərcümə saxlamaq
const saveTranslation = async (key, translations) => {
    const response = await fetch('/api/admin/translations/save', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            key: key,
            translation: translations // { az: "Salam", en: "Hello", ru: "Привет" }
        })
    });

    return await response.json();
};

// Tərcümə silmək
const deleteTranslation = async (key) => {
    const response = await fetch(`/api/admin/translations/${key}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });

    return await response.json();
};
```

## 🌐 Frontend Integration

### React Translation Hook

```jsx
// useTranslation.js
import { useState, useEffect, useContext, createContext } from 'react';

const TranslationContext = createContext();

export const TranslationProvider = ({ children }) => {
    const [translations, setTranslations] = useState({});
    const [currentLanguage, setCurrentLanguage] = useState('az');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadTranslations(currentLanguage);
        // Language-i localStorage-dən oxu
        const savedLanguage = localStorage.getItem('language');
        if (savedLanguage && savedLanguage !== currentLanguage) {
            setCurrentLanguage(savedLanguage);
        }
    }, [currentLanguage]);

    const loadTranslations = async (locale) => {
        try {
            setLoading(true);
            const response = await fetch(`/api/translations/${locale}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (response.ok) {
                const data = await response.json();
                setTranslations(data);
            }
        } catch (error) {
            console.error('Translations yüklənmədi:', error);
        } finally {
            setLoading(false);
        }
    };

    const changeLanguage = (locale) => {
        setCurrentLanguage(locale);
        localStorage.setItem('language', locale);
        loadTranslations(locale);
    };

    const t = (key, params = {}) => {
        let value = translations[key] || key;

        // Parametrləri əvəz et
        Object.keys(params).forEach(param => {
            value = value.replace(`:${param}`, params[param]);
        });

        return value;
    };

    return (
        <TranslationContext.Provider value={{
            t,
            currentLanguage,
            changeLanguage,
            loading,
            translations
        }}>
            {children}
        </TranslationContext.Provider>
    );
};

export const useTranslation = () => {
    const context = useContext(TranslationContext);
    if (!context) {
        throw new Error('useTranslation must be used within TranslationProvider');
    }
    return context;
};
```

### Language Selector Component

```jsx
// LanguageSelector.jsx
import React, { useState, useEffect } from 'react';
import { useTranslation } from './useTranslation';

const LanguageSelector = () => {
    const { currentLanguage, changeLanguage } = useTranslation();
    const [languages, setLanguages] = useState([]);

    useEffect(() => {
        fetchLanguages();
    }, []);

    const fetchLanguages = async () => {
        try {
            const response = await fetch('/api/languages', {
                headers: { 'Accept': 'application/json' }
            });

            if (response.ok) {
                const data = await response.json();
                setLanguages(data);
            }
        } catch (error) {
            console.error('Dillər yüklənmədi:', error);
        }
    };

    return (
        <div className="language-selector">
            <select
                value={currentLanguage}
                onChange={(e) => changeLanguage(e.target.value)}
                className="language-dropdown"
            >
                {languages.map(language => (
                    <option key={language.locale} value={language.locale}>
                        {language.name}
                    </option>
                ))}
            </select>
        </div>
    );
};

export default LanguageSelector;
```

### Translation Management Component

```jsx
// TranslationManager.jsx
import React, { useState, useEffect } from 'react';

const TranslationManager = () => {
    const [translations, setTranslations] = useState([]);
    const [languages, setLanguages] = useState([]);
    const [editingTranslation, setEditingTranslation] = useState(null);
    const [newTranslation, setNewTranslation] = useState({
        key: '',
        translations: {}
    });

    useEffect(() => {
        fetchTranslations();
        fetchLanguages();
    }, []);

    const fetchTranslations = async () => {
        try {
            const response = await fetch('/api/admin/translations', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                setTranslations(data.data);
            }
        } catch (error) {
            console.error('Tərcümələr yüklənmədi:', error);
        }
    };

    const fetchLanguages = async () => {
        try {
            const response = await fetch('/api/admin/languages', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                setLanguages(data.data);
            }
        } catch (error) {
            console.error('Dillər yüklənmədi:', error);
        }
    };

    const saveTranslation = async (key, translations) => {
        try {
            const response = await fetch('/api/admin/translations/save', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    key: key,
                    translation: translations
                })
            });

            if (response.ok) {
                fetchTranslations(); // Siyahını yenilə
                setEditingTranslation(null);
                setNewTranslation({ key: '', translations: {} });
            }
        } catch (error) {
            console.error('Tərcümə saxlanmadı:', error);
        }
    };

    const deleteTranslation = async (key) => {
        if (confirm('Bu tərcüməni silmək istədiyinizdən əminsiniz?')) {
            try {
                const response = await fetch(`/api/admin/translations/${key}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    fetchTranslations();
                }
            } catch (error) {
                console.error('Tərcümə silinmədi:', error);
            }
        }
    };

    return (
        <div className="translation-manager">
            <div className="header">
                <h2>Tərcümə İdarəetməsi</h2>
                <button
                    className="btn-primary"
                    onClick={() => setEditingTranslation('new')}
                >
                    Yeni Tərcümə
                </button>
            </div>

            {/* Yeni tərcümə formu */}
            {editingTranslation === 'new' && (
                <div className="translation-form">
                    <h3>Yeni Tərcümə</h3>
                    <input
                        type="text"
                        placeholder="Tərcümə açarı (key)"
                        value={newTranslation.key}
                        onChange={(e) => setNewTranslation({
                            ...newTranslation,
                            key: e.target.value
                        })}
                    />

                    {languages.map(language => (
                        <div key={language.locale} className="language-input">
                            <label>{language.name} ({language.locale}):</label>
                            <textarea
                                value={newTranslation.translations[language.locale] || ''}
                                onChange={(e) => setNewTranslation({
                                    ...newTranslation,
                                    translations: {
                                        ...newTranslation.translations,
                                        [language.locale]: e.target.value
                                    }
                                })}
                            />
                        </div>
                    ))}

                    <div className="form-actions">
                        <button
                            onClick={() => saveTranslation(newTranslation.key, newTranslation.translations)}
                            className="btn-save"
                        >
                            Saxla
                        </button>
                        <button
                            onClick={() => setEditingTranslation(null)}
                            className="btn-cancel"
                        >
                            Ləğv et
                        </button>
                    </div>
                </div>
            )}

            {/* Tərcümə siyahısı */}
            <div className="translations-list">
                {translations.map(translation => (
                    <div key={translation.key} className="translation-item">
                        <div className="translation-key">
                            <strong>{translation.key}</strong>
                        </div>

                        <div className="translation-values">
                            {languages.map(language => (
                                <div key={language.locale} className="language-value">
                                    <span className="language-label">{language.locale}:</span>
                                    <span className="translation-text">
                                        {/* Burda translation value-ni göstər */}
                                        {translation.value || 'Tərcümə yoxdur'}
                                    </span>
                                </div>
                            ))}
                        </div>

                        <div className="translation-actions">
                            <button
                                className="btn-edit"
                                onClick={() => setEditingTranslation(translation.key)}
                            >
                                Redaktə
                            </button>
                            <button
                                className="btn-delete"
                                onClick={() => deleteTranslation(translation.key)}
                            >
                                Sil
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default TranslationManager;
```

## 🛠 Service Metodları

### TranslationService Class

```php
namespace App\Services\Module;

class TranslationService extends BaseCrudService
{
    /**
     * Tərcümə saxlamaq (bütün dillər üçün)
     */
    public function save(array $data): bool

    /**
     * Tərcümə silmək
     */
    public function destroy(string $key): bool

    /**
     * Tərcümə əldə etmək
     */
    public function getTranslation(
        string $key, 
        ?string $locale = null, 
        ?string $default = null, 
        bool $notCached = false
    ): string

    /**
     * Tərcümə təyin etmək
     */
    public function setTranslation(
        string $key, 
        string $value, 
        ?string $locale = null
    ): Translate

    /**
     * Cache təmizləmə
     */
    public function clearTranslationCache(string $key, ?string $locale = null): void

    /**
     * Bütün cache təmizləmə
     */
    public function clearAllTranslationCache(): void

    /**
     * Bütün dillər
     */
    public function getAllLanguages(): Collection

    /**
     * Aktiv dillər
     */
    public function getActiveLanguages(): Collection

    /**
     * Aktiv dillər tərcümələri ilə
     */
    public function getActiveLanguagesWithTranslate(): Collection

    /**
     * Default dil təyin etmək
     */
    public function setDefaultLanguage(string $locale): bool

    /**
     * Tərcümələri import etmək
     */
    public function importTranslations(array $translations, ?string $locale = null): void

    /**
     * Tərcümələri export etmək
     */
    public function exportTranslations(?string $locale = null): array

    /**
     * Cari dil
     */
    public function getCurrentLanguage(): string
}
```

### LanguageService Class

```php
namespace App\Services\Module;

class LanguageService extends BaseCrudService
{
    // BaseCrudService-dən miras alınan bütün metodlar:
    
    /**
     * Yeni dil yaratma
     */
    public function create(array $data): Language

    /**
     * Dil məlumat yeniləmə
     */
    public function update(int $id, array $data): Language

    /**
     * Dil silmə
     */
    public function delete(int $id): bool

    /**
     * ID ilə dil tapma
     */
    public function findById(int $id): Language

    /**
     * Pagination və filter
     */
    public function paginateAndFilter(): LengthAwarePaginator

    /**
     * Aktiv dillər
     */
    public function findActiveList(): Collection
}
```

## 🏗️ Repository Metodları

### TranslationRepository Class

```php
namespace App\Repositories\Module;

class TranslationRepository extends BaseRepository
{
    /**
     * Pagination və filter (key üzrə qruplaşdırılmış)
     */
    public function paginateAndFilter(): LengthAwarePaginator|Collection
}
```

### LanguageRepository Class

```php
namespace App\Repositories\Module;

class LanguageRepository extends BaseRepository
{
    /**
     * Dilin tərcümələri ilə birlikdə
     */
    public function getLanguagesWithTranslates(string $locale): Collection
}
```

## 📁 JSON File Structure

### Translation JSON Files

Tərcümə faylları `public/lang/translations/` qovluğunda saxlanılır:

```
public/
└── lang/
    └── translations/
        ├── az.json     // Azərbaycan dili
        ├── en.json     // İngilis dili
        └── ru.json     // Rus dili
```

### JSON File Example (az.json)

```json
{
    "enums": {
        "user_status": {
            "active": "Aktiv",
            "inactive": "Aktiv deyil"
        }
    },
    "notification": {
        "user_not_found": "İstifadəçi tapılmadı"
    },
    "validation": {
        "password": {
            "min_length": "Şifrə minimum :length simvol olmalıdır"
        }
    }
}
```

## 🌱 Seeding System

### TranslationSeeder Class

```php
namespace Database\Seeders;

class TranslationSeeder extends Seeder
{
    /**
     * Əsas seed prosesi
     */
    public function run(): void

    /**
     * Dilləri seed etmək
     */
    protected function seedLanguages(): void

    /**
     * Tərcümələri seed etmək
     */
    protected function seedTranslations(): void

    /**
     * JSON faylı mövcudluq yoxlaması
     */
    protected function localeFileExists(string $locale): bool

    /**
     * JSON fayldan tərcümə oxuma
     */
    protected function getTranslationsForLocale(string $locale): array

    /**
     * Nested JSON strukturunu flat etmək
     */
    protected function flattenTranslations(array $translations, string $prefix = ''): array

    /**
     * Tərcümələri sinxronizasiya etmək
     */
    protected function syncTranslationsForLocale(string $locale, array $translationsArray): void

    /**
     * Dilin bütün tərcümələrini silmək
     */
    protected function deleteAllTranslationsForLocale(string $locale): void
}
```

### Seeding Commands

```bash
# Tərcümələri seed etmək
php artisan db:seed --class=TranslationSeeder

# Yalnız müəyyən dil üçün
php artisan translations:sync az

# Bütün cache təmizləmək
php artisan cache:clear
```

## 🎯 Helper Functions

### Translation Helper

```php
// Tərcümə əldə etmək
function t(string $key, array $replace = [], ?string $locale = null): string
{
    return app(TranslationService::class)->getTranslation($key, $locale, null, false);
}

// Cari dil
function currentLang(): string
{
    return app()->getLocale();
}

// Parametrli tərcümə
function trans_choice(string $key, int $number, array $replace = [], ?string $locale = null): string
{
    $translation = t($key, $replace, $locale);
    
    // Pluralization logic
    if ($number == 1) {
        return str_replace(':count', $number, $translation);
    }
    
    return str_replace(':count', $number, $translation);
}
```

### Usage Examples

```php
// Sadə istifadə
echo t('notification.user_not_found');
// Output: "İstifadəçi tapılmadı"

// Parametrli istifadə
echo t('validation.password.min_length', ['length' => 8]);
// Output: "Şifrə minimum 8 simvol olmalıdır"

// Müəyyən dil üçün
echo t('notification.welcome', [], 'en');
// Output: "Welcome"

// Nested key istifadəsi
echo t('enums.user_status.active');
// Output: "Aktiv"
```

## 🚀 Performance Optimizasiyası

### Cache Strategy

```php
// TranslationService cache implementasiyası
protected function getCacheKey(string $key, string $locale): string
{
    return "translation:{$locale}:{$key}";
}

public function getTranslation(string $key, ?string $locale = null, ?string $default = null, bool $notCached = false): string
{
    $locale = $locale ?: Helper::language();
    $cacheKey = $this->getCacheKey($key, $locale);

    if ($notCached) {
        return Translate::getTranslation($key, $locale);
    }

    return Cache::remember($cacheKey, now()->addDay(), function () use ($key, $locale, $default) {
        $translation = Translate::getTranslation($key, $locale);
        return $translation !== $key ? $translation : ($default ?: $key);
    });
}
```

### Database Indexing

```sql
-- Performance üçün indekslər
CREATE INDEX idx_translates_locale ON translates(locale);
CREATE INDEX idx_translates_key ON translates(key);
CREATE INDEX idx_translates_key_locale ON translates(key, locale);
CREATE INDEX idx_languages_locale ON languages(locale);
CREATE INDEX idx_languages_active ON languages(is_active);
CREATE INDEX idx_languages_default ON languages(is_default);
```

### Caching Best Practices

```php
// Cache keys
"translation:{locale}:{key}"     // Tək tərcümə
"languages:active"               // Aktiv dillər
"languages:all"                  // Bütün dillər
"translations:{locale}:all"      // Dilin bütün tərcümələri

// Cache clearing
TranslationService::clearTranslationCache($key, $locale);  // Tək tərcümə
TranslationService::clearAllTranslationCache();           // Bütün cache
```

## 🔧 Configuration

### Language Configuration

```php
// config/app.php
'locale' => env('APP_LOCALE', 'az'),
'fallback_locale' => env('APP_FALLBACK_LOCALE', 'az'),
'faker_locale' => env('APP_FAKER_LOCALE', 'az_AZ'),

// Mövcud dillər
'available_locales' => ['az', 'en', 'ru'],

// Translation cache ttl  
'translation_cache_ttl' => env('TRANSLATION_CACHE_TTL', 86400), // 24 saat
```

### Environment Variables

```env
# Language Settings
APP_LOCALE=az
APP_FALLBACK_LOCALE=az
APP_FAKER_LOCALE=az_AZ

# Translation Cache
TRANSLATION_CACHE_TTL=86400
TRANSLATION_JSON_PATH=public/lang/translations/

# Seeding
TRANSLATION_SEED_ON_MIGRATE=true
```

## 🧪 Testing

### Unit Tests

```php
// TranslationServiceTest.php
class TranslationServiceTest extends TestCase
{
    public function test_can_get_translation()
    {
        $service = app(TranslationService::class);
        
        $service->setTranslation('test.key', 'Test Value', 'az');
        
        $translation = $service->getTranslation('test.key', 'az');
        
        $this->assertEquals('Test Value', $translation);
    }

    public function test_can_set_translation()
    {
        $service = app(TranslationService::class);
        
        $result = $service->setTranslation('new.key', 'New Value', 'en');
        
        $this->assertInstanceOf(Translate::class, $result);
        $this->assertEquals('new.key', $result->key);
        $this->assertEquals('New Value', $result->value);
        $this->assertEquals('en', $result->locale);
    }

    public function test_translation_caching()
    {
        $service = app(TranslationService::class);
        
        // Cache-də olmayan tərcümə
        $translation1 = $service->getTranslation('cache.test', 'az');
        
        // İkinci dəfə - cache-dən gəlməli
        $translation2 = $service->getTranslation('cache.test', 'az');
        
        $this->assertEquals($translation1, $translation2);
    }

    public function test_can_clear_translation_cache()
    {
        $service = app(TranslationService::class);
        
        $service->setTranslation('cache.clear.test', 'Old Value', 'az');
        $service->clearTranslationCache('cache.clear.test', 'az');
        
        // Cache təmizləndikdən sonra yeni dəyər
        Translate::where('key', 'cache.clear.test')
            ->where('locale', 'az')
            ->update(['value' => 'New Value']);
            
        $translation = $service->getTranslation('cache.clear.test', 'az', null, true);
        
        $this->assertEquals('New Value', $translation);
    }

    public function test_parameter_replacement()
    {
        $service = app(TranslationService::class);
        
        $service->setTranslation('param.test', 'Hello :name, you have :count messages', 'en');
        
        $translation = $service->getTranslation('param.test', 'en');
        $result = str_replace([':name', ':count'], ['John', '5'], $translation);
        
        $this->assertEquals('Hello John, you have 5 messages', $result);
    }

    public function test_fallback_to_key_when_translation_not_found()
    {
        $service = app(TranslationService::class);
        
        $translation = $service->getTranslation('non.existent.key', 'az');
        
        $this->assertEquals('non.existent.key', $translation);
    }
}
```

### Feature Tests

```php
// TranslationApiTest.php
class TranslationApiTest extends TestCase
{
    public function test_admin_can_get_translations()
    {
        $admin = User::factory()->admin()->create();
        
        Translate::factory()->create([
            'key' => 'test.api.key',
            'value' => 'Test API Value',
            'locale' => 'az'
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/api/admin/translations');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['key', 'value', 'locale']
                ],
                'total'
            ]);
    }

    public function test_admin_can_save_translation()
    {
        $admin = User::factory()->admin()->create();

        $translationData = [
            'key' => 'new.api.key',
            'translation' => [
                'az' => 'Yeni API açarı',
                'en' => 'New API key',
                'ru' => 'Новый API ключ'
            ]
        ];

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/translations/save', $translationData);

        $response->assertOk();
        
        $this->assertDatabaseHas('translates', [
            'key' => 'new.api.key',
            'value' => 'Yeni API açarı',
            'locale' => 'az'
        ]);
    }

    public function test_admin_can_delete_translation()
    {
        $admin = User::factory()->admin()->create();
        
        Translate::factory()->create([
            'key' => 'delete.test.key',
            'is_system' => false
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson('/api/admin/translations/delete.test.key');

        $response->assertOk();
        
        $this->assertDatabaseMissing('translates', [
            'key' => 'delete.test.key'
        ]);
    }

    public function test_user_cannot_access_admin_translations()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/admin/translations');

        $response->assertStatus(403);
    }

    public function test_public_can_get_active_languages()
    {
        Language::factory()->create(['locale' => 'az', 'is_active' => true]);
        Language::factory()->create(['locale' => 'en', 'is_active' => true]);
        Language::factory()->create(['locale' => 'ru', 'is_active' => false]);

        $response = $this->getJson('/api/languages');

        $response->assertOk()
            ->assertJsonCount(2);
    }
}
```

### Language Tests

```php
// LanguageServiceTest.php
class LanguageServiceTest extends TestCase
{
    public function test_can_create_language()
    {
        $service = app(LanguageService::class);
        
        $languageData = [
            'name' => 'Türkçe',
            'locale' => 'tr',
            'is_active' => true,
            'is_default' => false
        ];
        
        $language = $service->create($languageData);
        
        $this->assertInstanceOf(Language::class, $language);
        $this->assertEquals('tr', $language->locale);
        $this->assertTrue($language->is_active);
    }

    public function test_can_set_default_language()
    {
        Language::factory()->create(['locale' => 'az', 'is_default' => true]);
        Language::factory()->create(['locale' => 'en', 'is_default' => false]);
        
        Language::setDefault('en');
        
        $this->assertFalse(Language::where('locale', 'az')->first()->is_default);
        $this->assertTrue(Language::where('locale', 'en')->first()->is_default);
    }

    public function test_get_active_languages()
    {
        Language::factory()->create(['locale' => 'az', 'is_active' => true]);
        Language::factory()->create(['locale' => 'en', 'is_active' => true]);
        Language::factory()->create(['locale' => 'ru', 'is_active' => false]);
        
        $activeLanguages = Language::active()->get();
        
        $this->assertCount(2, $activeLanguages);
    }

    public function test_get_default_language()
    {
        Language::factory()->create(['locale' => 'az', 'is_default' => true]);
        Language::factory()->create(['locale' => 'en', 'is_default' => false]);
        
        $defaultLanguage = Language::getDefault();
        
        $this->assertEquals('az', $defaultLanguage->locale);
    }
}
```

## 🔍 Monitoring və Debugging

### Translation Debug Helper

```php
// App\Helpers\TranslationDebugger
class TranslationDebugger
{
    public static function getMissingTranslations(string $locale): array
    {
        $allKeys = Translate::distinct('key')->pluck('key');
        $localeKeys = Translate::where('locale', $locale)->pluck('key');
        
        return $allKeys->diff($localeKeys)->toArray();
    }

    public static function getUnusedTranslations(string $locale): array
    {
        // Bu metodla kod içərisində istifadə olunmayan tərcümələri tapırıq
        $allTranslations = Translate::where('locale', $locale)->pluck('key');
        $usedTranslations = collect();
        
        // Kod fayllarını scan edərək istifadə olunan açarları tapırıq
        $files = File::allFiles(app_path());
        foreach ($files as $file) {
            $content = File::get($file);
            preg_match_all("/t\('([^']+)'\)/", $content, $matches);
            $usedTranslations = $usedTranslations->merge($matches[1]);
        }
        
        return $allTranslations->diff($usedTranslations->unique())->toArray();
    }

    public static function validateTranslationCompleteness(): array
    {
        $languages = Language::active()->pluck('locale');
        $allKeys = Translate::distinct('key')->pluck('key');
        
        $report = [];
        
        foreach ($languages as $locale) {
            $localeKeys = Translate::where('locale', $locale)->pluck('key');
            $missing = $allKeys->diff($localeKeys);
            
            $report[$locale] = [
                'total_keys' => $allKeys->count(),
                'translated_keys' => $localeKeys->count(),
                'missing_keys' => $missing->count(),
                'completion_percentage' => round(($localeKeys->count() / $allKeys->count()) * 100, 2),
                'missing_list' => $missing->toArray()
            ];
        }
        
        return $report;
    }
}
```

### Artisan Commands

```php
// app/Console/Commands/TranslationCommands.php

// Tərcümə tam olmama yoxlaması
class CheckTranslationCompleteness extends Command
{
    protected $signature = 'translations:check {--locale=}';
    protected $description = 'Check translation completeness for languages';

    public function handle()
    {
        $locale = $this->option('locale');
        
        if ($locale) {
            $missing = TranslationDebugger::getMissingTranslations($locale);
            $this->info("Missing translations for {$locale}: " . count($missing));
            
            if (!empty($missing)) {
                $this->table(['Missing Keys'], array_map(fn($key) => [$key], $missing));
            }
        } else {
            $report = TranslationDebugger::validateTranslationCompleteness();
            
            $tableData = [];
            foreach ($report as $locale => $data) {
                $tableData[] = [
                    $locale,
                    $data['translated_keys'],
                    $data['total_keys'],
                    $data['completion_percentage'] . '%',
                    $data['missing_keys']
                ];
            }
            
            $this->table(
                ['Language', 'Translated', 'Total', 'Completion', 'Missing'],
                $tableData
            );
        }
    }
}

// Tərcümə cache təmizləmə
class ClearTranslationCache extends Command
{
    protected $signature = 'translations:cache-clear {--key=} {--locale=}';
    protected $description = 'Clear translation cache';

    public function handle()
    {
        $service = app(TranslationService::class);
        
        $key = $this->option('key');
        $locale = $this->option('locale');
        
        if ($key && $locale) {
            $service->clearTranslationCache($key, $locale);
            $this->info("Cache cleared for key: {$key}, locale: {$locale}");
        } elseif ($key) {
            // Bütün dillər üçün bu key-i təmizlə
            $languages = Language::active()->pluck('locale');
            foreach ($languages as $lang) {
                $service->clearTranslationCache($key, $lang);
            }
            $this->info("Cache cleared for key: {$key} in all languages");
        } else {
            $service->clearAllTranslationCache();
            $this->info("All translation cache cleared");
        }
    }
}

// JSON fayllarından sync
class SyncTranslationsFromJson extends Command
{
    protected $signature = 'translations:sync {--locale=}';
    protected $description = 'Sync translations from JSON files';

    public function handle()
    {
        $locale = $this->option('locale');
        
        if ($locale) {
            $this->syncLocale($locale);
        } else {
            $languages = Language::active()->pluck('locale');
            foreach ($languages as $lang) {
                $this->syncLocale($lang);
            }
        }
    }

    private function syncLocale(string $locale)
    {
        $seeder = new TranslationSeeder(app(TranslationService::class));
        
        try {
            // Protected metodlara çıxış üçün reflection istifadə
            $reflection = new ReflectionClass($seeder);
            
            $syncMethod = $reflection->getMethod('syncTranslationsForLocale');
            $syncMethod->setAccessible(true);
            
            $getTranslationsMethod = $reflection->getMethod('getTranslationsForLocale');
            $getTranslationsMethod->setAccessible(true);
            
            $translations = $getTranslationsMethod->invoke($seeder, $locale);
            $syncMethod->invoke($seeder, $locale, $translations);
            
            $this->info("Translations synced for locale: {$locale}");
        } catch (Exception $e) {
            $this->error("Failed to sync translations for {$locale}: " . $e->getMessage());
        }
    }
}
```

## 📈 Analytics və Reporting

### Translation Usage Analytics

```php
// App\Services\TranslationAnalytics
class TranslationAnalytics
{
    public function getUsageStats(): array
    {
        return [
            'total_keys' => Translate::distinct('key')->count(),
            'total_translations' => Translate::count(),
            'languages_count' => Language::active()->count(),
            'avg_translations_per_language' => round(Translate::count() / Language::active()->count(), 2),
            'system_translations' => Translate::where('is_system', true)->count(),
            'custom_translations' => Translate::where('is_system', false)->count(),
        ];
    }

    public function getLanguageStats(): array
    {
        return Language::withCount('translates')
            ->get()
            ->map(function ($language) {
                return [
                    'language' => $language->name,
                    'locale' => $language->locale,
                    'is_active' => $language->is_active,
                    'is_default' => $language->is_default,
                    'translation_count' => $language->translates_count,
                    'completion_percentage' => $this->getCompletionPercentage($language->locale)
                ];
            })->toArray();
    }

    public function getTopMissingTranslations(int $limit = 10): array
    {
        $allKeys = Translate::distinct('key')->pluck('key');
        $languages = Language::active()->pluck('locale');
        
        $missingStats = [];
        
        foreach ($allKeys as $key) {
            $translatedIn = Translate::where('key', $key)->pluck('locale');
            $missingIn = $languages->diff($translatedIn);
            
            if ($missingIn->count() > 0) {
                $missingStats[] = [
                    'key' => $key,
                    'missing_in_languages' => $missingIn->count(),
                    'missing_languages' => $missingIn->toArray(),
                    'completion_percentage' => round((($languages->count() - $missingIn->count()) / $languages->count()) * 100, 2)
                ];
            }
        }
        
        // Missing count-a görə sıralayıb limit tətbiq et
        return collect($missingStats)
            ->sortByDesc('missing_in_languages')
            ->take($limit)
            ->values()
            ->toArray();
    }

    private function getCompletionPercentage(string $locale): float
    {
        $totalKeys = Translate::distinct('key')->count();
        $localeKeys = Translate::where('locale', $locale)->count();
        
        return $totalKeys > 0 ? round(($localeKeys / $totalKeys) * 100, 2) : 0;
    }
}
```

## 🔄 Migration və Deployment

### Translation Migration

```php
// Migration command
class MigrateTranslations extends Command
{
    protected $signature = 'translations:migrate {--from=} {--to=}';
    protected $description = 'Migrate translations from old system to new system';

    public function handle()
    {
        $from = $this->option('from') ?: 'json';
        $to = $this->option('to') ?: 'database';
        
        if ($from === 'json' && $to === 'database') {
            $this->migrateFromJsonToDatabase();
        } elseif ($from === 'database' && $to === 'json') {
            $this->migrateFromDatabaseToJson();
        }
    }

    private function migrateFromJsonToDatabase()
    {
        $this->info('Migrating translations from JSON files to database...');
        
        $languages = Language::active()->get();
        $migrated = 0;
        
        foreach ($languages as $language) {
            $jsonPath = public_path("lang/translations/{$language->locale}.json");
            
            if (File::exists($jsonPath)) {
                $translations = json_decode(File::get($jsonPath), true);
                $flattened = $this->flattenArray($translations);
                
                foreach ($flattened as $key => $value) {
                    Translate::updateOrCreate(
                        ['key' => $key, 'locale' => $language->locale],
                        ['value' => $value, 'is_system' => true]
                    );
                    $migrated++;
                }
                
                $this->info("Migrated {$language->locale}: " . count($flattened) . " translations");
            } else {
                $this->warn("JSON file not found for locale: {$language->locale}");
            }
        }
        
        $this->info("Total migrated: {$migrated} translations");
    }

    private function migrateFromDatabaseToJson()
    {
        $this->info('Migrating translations from database to JSON files...');
        
        $languages = Language::active()->get();
        
        foreach ($languages as $language) {
            $translations = Translate::where('locale', $language->locale)
                ->pluck('value', 'key')
                ->toArray();
                
            $nested = $this->unflattenArray($translations);
            
            $jsonPath = public_path("lang/translations/{$language->locale}.json");
            
            File::put($jsonPath, json_encode($nested, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $this->info("Exported {$language->locale}: " . count($translations) . " translations");
        }
    }

    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;
            
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }

    private function unflattenArray(array $array): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $keys = explode('.', $key);
            $temp = &$result;
            
            foreach ($keys as $subKey) {
                $temp = &$temp[$subKey];
            }
            
            $temp = $value;
        }
        
        return $result;
    }
}
```

## 📋 TODO və Gələcək Təkmilləşdirmələr

- [ ] **Translation Management UI** - Daha advanced admin interface
- [ ] **Translation Workflows** - Tərcümə təsdiq prosesi
- [ ] **Machine Translation Integration** - Google Translate API
- [ ] **Translation Memory** - Təkrarlanan tərcümələrin avtomatik təklifi
- [ ] **Version Control** - Tərcümə versiyaları və tarixçəsi
- [ ] **Collaborative Translation** - Çoxlu tərcüməçi dəstəyi
- [ ] **Translation Validation** - Tərcümə keyfiyyət yoxlaması
- [ ] **Export/Import Tools** - Excel, CSV format dəstəyi
- [ ] **Real-time Translation** - WebSocket əsaslı canlı yeniləmə
- [ ] **Translation Statistics** - Ətraflı analitik dashboard

## ⚠️ Best Practices və Tövsiyələr

### Development Best Practices

1. **Key Naming Convention**: Hierarxik struktur istifadə edin
   ```php
   // ✅ Yaxşı
   'validation.password.min_length'
   'enums.user_status.active'
   
   // ❌ Pis
   'validation_password_min_length'
   'user_status_active'
   ```

2. **Parameter Usage**: Dinamik məlumatlar üçün parametr istifadə edin
   ```php
   // ✅ Yaxşı
   'welcome_message' => 'Xoş gəldin :name!'
   
   // ❌ Pis
   'welcome_message_john' => 'Xoş gəldin John!'
   ```

3. **Cache Management**: Tərcümə dəyişikliklərindən sonra cache təmizləyin
   ```php
   $translationService->setTranslation($key, $value, $locale);
   $translationService->clearTranslationCache($key, $locale);
   ```

4. **Fallback Strategy**: Default dil üçün fallback təmin edin
   ```php
   $translation = t($key, [], $currentLocale) ?: t($key, [], $defaultLocale);
   ```

### Performance Tips

1. **Batch Operations**: Çoxlu tərcümə əməliyyatları üçün batch istifadə edin
2. **Cache Warming**: Sistem başlangıcında əsas tərcümələri cache-ləyin
3. **Lazy Loading**: İstənilən zaman tərcümələri yükləyin
4. **Database Indexing**: Axtarış sahələrində indeks istifadə edin

### Security Considerations

1. **Input Validation**: İstifadəçi tərcümələrini validate edin
2. **XSS Protection**: HTML tərcümələrində XSS qoruma tətbiq edin
3. **Permission Control**: Tərcümə dəyişikliyi üçün icazə sistemi
4. **Audit Trail**: Tərcümə dəyişikliklərini loglayın

## 🤝 Töhfə və Support

Bu service-in inkişafında iştirak etmək üçün:
1. Bug report-lar göndərin
2. Yeni dil dəstəyi əlavə edin
3. Translation quality təkmilləşdirmələri təklif edin
4. Dokumentasiyanı yeniləyin

---

**Son yenilənmə**: 2025-01-15  
**Versiya**: 1.0.0  
**Laravel versiyası**: 12.x
