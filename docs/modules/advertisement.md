# Advertisement Service Documentation

## 📋 Ümumi Məlumat

Advertisement Service Laravel 12 layihəsində reklam bannerlərinin idarə edilməsini təmin edir. Bu service çoxdilli reklam sistemi, müxtəlif yerləşmə mövqeləri, göstərmə növləri və avtomatik view tracking dəstəkləyir.

## 🚀 Xüsusiyyətlər

- ✅ **Multi-Language Support** - Çoxdilli reklam məzmunu
- ✅ **Flexible Positioning** - 11 müxtəlif yerləşmə mövqeyi
- ✅ **Display Types** - Ana səhifə, bütün səhifələr və seçilmiş kateqoriyalar
- ✅ **Image Management** - Base64 və avtomatik image upload
- ✅ **View Tracking** - Avtomatik baxış sayının hesablanması
- ✅ **Expiry Management** - Avtomatik müddət bitməsi
- ✅ **Category Targeting** - Seçilmiş kateqoriyalarda göstərmə
- ✅ **Color Customization** - Background rəng tənzimləmələri
- ✅ **Admin Dashboard** - Tam administrativ panel
- ✅ **Responsive Design** - Web və mobil cihazlar üçün ayrı mövqelər

## 📊 Məlumat Bazası Strukturu

### `advertisements` cədvəli
```sql
CREATE TABLE advertisements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) NOT NULL UNIQUE,
    position VARCHAR(255) NOT NULL,           -- Yerləşmə mövqeyi
    expiry_date TIMESTAMP NULL,               -- Bitmə tarixi
    background_color VARCHAR(255) NULL,       -- Arxa plan rəngi
    display_type VARCHAR(255) NOT NULL,       -- Göstərmə növü
    selected_categories JSON NULL,            -- Seçilmiş kateqoriyalar
    translates JSON NOT NULL,                 -- Çoxdilli məlumatlar
    is_active BOOLEAN DEFAULT TRUE,           -- Status
    views INTEGER DEFAULT 0,                  -- Baxış sayı
    created_by BIGINT UNSIGNED NULL,          -- Yaradıcı
    updated_by BIGINT UNSIGNED NULL,          -- Yeniləyici
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_position (position),
    INDEX idx_display_type (display_type),
    INDEX idx_is_active (is_active),
    INDEX idx_expiry_date (expiry_date),
    INDEX idx_views (views)
);
```

### Çoxdilli Structure (translates column)
```json
{
    "az": {
        "link": "https://example.com/az",
        "photo": "base64_image_data_or_path"
    },
    "en": {
        "link": "https://example.com/en", 
        "photo": "base64_image_data_or_path"
    },
    "ru": {
        "link": "https://example.com/ru",
        "photo": "base64_image_data_or_path"
    }
}
```

## 🎯 Advertisement Positions & Display Types

### Position Növləri (AdvertisementPositionEnum)

| Position Code | Açıqlama | Platform |
|---------------|----------|----------|
| `web_top` | Səhifənin yuxarı hissəsi | Web |
| `mobile_top` | Səhifənin yuxarı hissəsi | Mobile |
| `listing_top` | Elan səhifəsinin yuxarısı | Web |
| `listing_bottom` | Elan səhifəsinin aşağısı | Web |
| `listing_right` | Elan səhifəsinin sağ tərəfi | Web |
| `listing_mobile_top` | Elan səhifəsinin yuxarısı | Mobile |
| `listing_mobile_bottom` | Elan səhifəsinin aşağısı | Mobile |
| `listing_mobile_right` | Elan səhifəsinin sağ tərəfi | Mobile |
| `similar_after_mobile` | Bənzər elanlardan sonra | Mobile |
| `similar_after_web` | Bənzər elanlardan sonra | Web |
| `latest_listings` | Son elanlar bölməsində | Web/Mobile |

### Display Types (AdvertisementDisplayTypeEnum)

| Type | Açıqlama |
|------|----------|
| `home_only` | Yalnız ana səhifədə göstər |
| `all_pages` | Bütün səhifələrdə göstər |
| `selected_categories` | Seçilmiş kateqoriyalarda göstər |

## 🔗 API Endpoints

### Admin Panel Endpoints

| HTTP Method | Endpoint | Açıqlama | Permission |
|-------------|----------|----------|------------|
| `GET` | `/api/admin/advertisements` | Reklam siyahısı | `advertisement_read` |
| `POST` | `/api/admin/advertisements` | Yeni reklam yaratma | `advertisement_create` |
| `GET` | `/api/admin/advertisements/{id}` | Reklam ətraflı məlumat | `advertisement_read` |
| `PUT` | `/api/admin/advertisements/{id}` | Reklam məlumat yeniləmə | `advertisement_update` |
| `DELETE` | `/api/admin/advertisements/{id}` | Reklam silmə | `advertisement_delete` |
| `POST` | `/api/admin/advertisements/{id}/action` | Status dəyişikliği | `advertisement_status` |

### Filter Endpoints

| HTTP Method | Endpoint | Açıqlama |
|-------------|----------|----------|
| `GET` | `/api/admin/advertisements/filters` | Filter optionları |

## 💻 İstifadə Nümunələri

### Admin Panel - Reklam Yaratma

```javascript
// Yeni reklam yaratma
const createAdvertisement = async (adData) => {
    const formData = {
        position: 'web_top',
        display_type: 'home_only',
        expiry_date: '2025-12-31',
        background_color: '#ffffff',
        selected_categories: [], // selected_categories display_type üçün
        translates: {
            az: {
                link: 'https://example.com/az',
                photo: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...' // base64 image
            },
            en: {
                link: 'https://example.com/en',
                photo: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...'
            },
            ru: {
                link: 'https://example.com/ru', 
                photo: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...'
            }
        }
    };

    try {
        const response = await fetch('/api/admin/advertisements', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        if (response.ok) {
            const result = await response.json();
            console.log('Reklam uğurla yaradıldı:', result);
            return result;
        } else {
            const error = await response.json();
            console.error('Xəta:', error);
        }
    } catch (error) {
        console.error('Network xətası:', error);
    }
};
```

### Reklam Siyahısını Əldə Etmə (Filtrlə)

```javascript
// Filtrlənmiş reklam siyahısı
const getAdvertisements = async (filters = {}) => {
    const params = new URLSearchParams({
        page: filters.page || 1,
        per_page: filters.per_page || 15,
        ...(filters.position && { position: filters.position }),
        ...(filters.display_type && { display_type: filters.display_type }),
        ...(filters.search && { search: filters.search })
    });

    const response = await fetch(`/api/admin/advertisements?${params}`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });

    const result = await response.json();
    return result;
};

// İstifadə nümunəsi
const ads = await getAdvertisements({
    position: 'web_top',
    display_type: 'home_only',
    page: 1
});
```

### Reklam Status Dəyişikliyi

```javascript
// Reklam statusunu dəyişmə
const changeAdStatus = async (adId, isActive) => {
    const response = await fetch(`/api/admin/advertisements/${adId}/action`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ is_active: isActive })
    });

    const result = await response.json();
    return result;
};
```

## 🌐 Frontend Implementation

### React Advertisement Manager

```jsx
// AdvertisementManager.jsx
import React, { useState, useEffect } from 'react';

const AdvertisementManager = () => {
    const [advertisements, setAdvertisements] = useState([]);
    const [filters, setFilters] = useState({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchAdvertisements();
        fetchFilterOptions();
    }, []);

    const fetchAdvertisements = async () => {
        try {
            const response = await fetch('/api/admin/advertisements', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                setAdvertisements(data.data);
            }
        } catch (error) {
            console.error('Reklamlar yüklənmədi:', error);
        } finally {
            setLoading(false);
        }
    };

    const fetchFilterOptions = async () => {
        try {
            const response = await fetch('/api/admin/advertisements/filters', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                setFilters(data);
            }
        } catch (error) {
            console.error('Filter seçimləri yüklənmədi:', error);
        }
    };

    const handleStatusChange = async (adId, currentStatus) => {
        try {
            const response = await fetch(`/api/admin/advertisements/${adId}/action`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ is_active: !currentStatus })
            });

            if (response.ok) {
                // Local state-i yeniləyirik
                setAdvertisements(prev => 
                    prev.map(ad => 
                        ad.id === adId 
                            ? { ...ad, is_active: !currentStatus }
                            : ad
                    )
                );
            }
        } catch (error) {
            console.error('Status dəyişdirilmədi:', error);
        }
    };

    if (loading) return <div>Yüklənir...</div>;

    return (
        <div className="advertisement-manager">
            <div className="header">
                <h2>Reklam İdarəetməsi</h2>
                <button className="btn-primary">Yeni Reklam</button>
            </div>

            <div className="filters">
                <select onChange={(e) => console.log('Position filter:', e.target.value)}>
                    <option value="">Bütün Mövqelər</option>
                    {filters.positions?.map(position => (
                        <option key={position.id} value={position.id}>
                            {position.name}
                        </option>
                    ))}
                </select>

                <select onChange={(e) => console.log('Display type filter:', e.target.value)}>
                    <option value="">Bütün Növlər</option>
                    {filters.display_types?.map(type => (
                        <option key={type.id} value={type.id}>
                            {type.name}
                        </option>
                    ))}
                </select>
            </div>

            <div className="advertisement-list">
                {advertisements.length === 0 ? (
                    <div className="no-ads">Reklam tapılmadı</div>
                ) : (
                    <div className="ads-grid">
                        {advertisements.map(ad => (
                            <div key={ad.id} className="ad-card">
                                <div className="ad-header">
                                    <span className="position-badge">
                                        {ad.position_text}
                                    </span>
                                    <span className={`status-badge ${ad.is_active ? 'active' : 'inactive'}`}>
                                        {ad.is_active ? 'Aktiv' : 'Deaktiv'}
                                    </span>
                                </div>

                                <div className="ad-content">
                                    {ad.translates?.az?.photo && (
                                        <img 
                                            src={ad.translates.az.photo} 
                                            alt="Advertisement"
                                            className="ad-image"
                                        />
                                    )}
                                    <div className="ad-info">
                                        <p><strong>Növ:</strong> {ad.display_type_text}</p>
                                        <p><strong>Bitmə:</strong> {ad.expiry_date}</p>
                                        <p><strong>Baxış:</strong> {ad.views}</p>
                                    </div>
                                </div>

                                <div className="ad-actions">
                                    <button className="btn-edit">Redaktə</button>
                                    <button 
                                        className={`btn-status ${ad.is_active ? 'deactivate' : 'activate'}`}
                                        onClick={() => handleStatusChange(ad.id, ad.is_active)}
                                    >
                                        {ad.is_active ? 'Deaktiv et' : 'Aktiv et'}
                                    </button>
                                    <button className="btn-delete">Sil</button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
};

export default AdvertisementManager;
```

### Advertisement Form Component

```jsx
// AdvertisementForm.jsx
import React, { useState } from 'react';

const AdvertisementForm = ({ advertisement, onSave, onCancel }) => {
    const [formData, setFormData] = useState({
        position: advertisement?.position || '',
        display_type: advertisement?.display_type || 'home_only',
        expiry_date: advertisement?.expiry_date || '',
        background_color: advertisement?.background_color || '#ffffff',
        selected_categories: advertisement?.selected_categories || [],
        translates: advertisement?.translates || {
            az: { link: '', photo: '' },
            en: { link: '', photo: '' },
            ru: { link: '', photo: '' }
        }
    });

    const handleImageUpload = (language, file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            setFormData(prev => ({
                ...prev,
                translates: {
                    ...prev.translates,
                    [language]: {
                        ...prev.translates[language],
                        photo: e.target.result
                    }
                }
            }));
        };
        reader.readAsDataURL(file);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        try {
            const url = advertisement 
                ? `/api/admin/advertisements/${advertisement.id}`
                : '/api/admin/advertisements';
            
            const method = advertisement ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method,
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            if (response.ok) {
                const result = await response.json();
                onSave(result);
            } else {
                const error = await response.json();
                console.error('Validation errors:', error);
            }
        } catch (error) {
            console.error('Form submit error:', error);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="advertisement-form">
            <div className="form-group">
                <label>Mövqe:</label>
                <select 
                    value={formData.position}
                    onChange={(e) => setFormData({...formData, position: e.target.value})}
                    required
                >
                    <option value="">Seçin</option>
                    <option value="web_top">Üst (Web)</option>
                    <option value="mobile_top">Üst (Mobil)</option>
                    <option value="listing_top">Elan (Üst)</option>
                    <option value="listing_bottom">Elan (Alt)</option>
                    <option value="listing_right">Elan (Sağ)</option>
                    {/* Digər optionlar */}
                </select>
            </div>

            <div className="form-group">
                <label>Göstərmə Növü:</label>
                <select 
                    value={formData.display_type}
                    onChange={(e) => setFormData({...formData, display_type: e.target.value})}
                    required
                >
                    <option value="home_only">Yalnız Ana Səhifə</option>
                    <option value="all_pages">Bütün Səhifələr</option>
                    <option value="selected_categories">Seçilmiş Kateqoriyalar</option>
                </select>
            </div>

            <div className="form-group">
                <label>Bitmə Tarixi:</label>
                <input
                    type="date"
                    value={formData.expiry_date}
                    onChange={(e) => setFormData({...formData, expiry_date: e.target.value})}
                    required
                />
            </div>

            <div className="form-group">
                <label>Arxa Plan Rəngi:</label>
                <input
                    type="color"
                    value={formData.background_color}
                    onChange={(e) => setFormData({...formData, background_color: e.target.value})}
                />
            </div>

            {/* Çoxdilli sahələr */}
            <div className="multilingual-section">
                <h3>Dil Məlumatları</h3>
                
                {['az', 'en', 'ru'].map(lang => (
                    <div key={lang} className="language-group">
                        <h4>{lang.toUpperCase()}</h4>
                        
                        <div className="form-group">
                            <label>Link:</label>
                            <input
                                type="url"
                                value={formData.translates[lang]?.link || ''}
                                onChange={(e) => setFormData({
                                    ...formData,
                                    translates: {
                                        ...formData.translates,
                                        [lang]: {
                                            ...formData.translates[lang],
                                            link: e.target.value
                                        }
                                    }
                                })}
                            />
                        </div>

                        <div className="form-group">
                            <label>Şəkil:</label>
                            <input
                                type="file"
                                accept="image/*"
                                onChange={(e) => handleImageUpload(lang, e.target.files[0])}
                                required={!advertisement}
                            />
                            {formData.translates[lang]?.photo && (
                                <img 
                                    src={formData.translates[lang].photo} 
                                    alt="Preview"
                                    className="image-preview"
                                />
                            )}
                        </div>
                    </div>
                ))}
            </div>

            <div className="form-actions">
                <button type="submit" className="btn-save">
                    {advertisement ? 'Yenilə' : 'Yaradır'}
                </button>
                <button type="button" onClick={onCancel} className="btn-cancel">
                    Ləğv et
                </button>
            </div>
        </form>
    );
};

export default AdvertisementForm;
```

## 🛠 Service Metodları

### AdvertisementService Class

```php
namespace App\Services\Module;

class AdvertisementService extends BaseCrudService
{
    // BaseCrudService-dən miras alınan metodlar:
    
    /**
     * Yeni reklam yaratma
     */
    public function create(array $data): Advertisement

    /**
     * Reklam məlumat yeniləmə
     */
    public function update(int $id, array $data): Advertisement

    /**
     * Reklam silmə
     */
    public function delete(int $id): bool

    /**
     * ID ilə reklam tapma
     */
    public function findById(int $id): Advertisement

    /**
     * Pagination və filter
     */
    public function paginateAndFilter(): LengthAwarePaginator

    /**
     * Aktiv reklamlar
     */
    public function findActiveList(): Collection

    /**
     * Status dəyişikliyi
     */
    public function changeStatus(int $id, array $request): Advertisement
}
```

## 🏗️ Repository Metodları

### AdvertisementRepository Class

```php
namespace App\Repositories\Module;

class AdvertisementRepository extends BaseRepository
{
    /**
     * Reklam yaratma (transaction ilə)
     */
    public function create(array $data): Advertisement

    /**
     * Reklam yeniləmə (image upload control ilə)
     */
    public function update(int $id, array $data): Advertisement

    /**
     * Filter optionları
     */
    public function filters(): array

    // Frontend üçün əlavə metodlar
    /**
     * Müəyyən mövqedə aktiv reklamlar
     */
    public function getActiveByPosition(string $position): Collection

    /**
     * Ana səhifə reklamları
     */
    public function getHomePageAds(): Collection

    /**
     * Kateqoriya əsaslı reklamlar
     */
    public function getByCategoryAds(array $categoryIds): Collection

    /**
     * View sayını artırma
     */
    public function incrementViews(int $id): void
}
```

### Frontend Üçün Əlavə Repository Metodları

```php
// AdvertisementRepository.php - əlavə metodlar
public function getActiveByPosition(string $position): Collection
{
    return $this->model
        ->where('position', $position)
        ->where('is_active', true)
        ->where(function($query) {
            $query->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>', now());
        })
        ->orderByDesc('created_at')
        ->get();
}

public function getHomePageAds(): Collection
{
    return $this->model
        ->whereIn('display_type', ['home_only', 'all_pages'])
        ->where('is_active', true)
        ->where(function($query) {
            $query->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>', now());
        })
        ->get();
}

public function getByCategoryAds(array $categoryIds): Collection
{
    return $this->model
        ->where(function($query) use ($categoryIds) {
            $query->where('display_type', 'all_pages')
                  ->orWhere(function($q) use ($categoryIds) {
                      $q->where('display_type', 'selected_categories')
                        ->where(function($subQ) use ($categoryIds) {
                            foreach ($categoryIds as $categoryId) {
                                $subQ->orWhereJsonContains('selected_categories', $categoryId);
                            }
                        });
                  });
        })
        ->where('is_active', true)
        ->where(function($query) {
            $query->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>', now());
        })
        ->get();
}

public function incrementViews(int $id): void
{
    $this->model->where('id', $id)->increment('views');
}
```

## 🎨 Frontend Display Implementation

### Advertisement Display Component

```jsx
// AdDisplay.jsx
import React, { useState, useEffect } from 'react';

const AdDisplay = ({ position, categoryIds = [] }) => {
    const [advertisements, setAdvertisements] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchAdvertisements();
    }, [position, categoryIds]);

    const fetchAdvertisements = async () => {
        try {
            let url = `/api/frontend/advertisements?position=${position}`;
            
            if (categoryIds.length > 0) {
                url += `&categories=${categoryIds.join(',')}`;
            }

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Language': localStorage.getItem('language') || 'az'
                }
            });

            if (response.ok) {
                const data = await response.json();
                setAdvertisements(data);
            }
        } catch (error) {
            console.error('Reklamlar yüklənmədi:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleAdClick = async (ad) => {
        // View sayını artır
        try {
            await fetch(`/api/frontend/advertisements/${ad.id}/view`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                }
            });
        } catch (error) {
            console.error('View count update failed:', error);
        }

        // Link-ə yönləndir
        if (ad.translates?.az?.link) {
            window.open(ad.translates.az.link, '_blank');
        }
    };

    if (loading) return <div>Yüklənir...</div>;
    if (advertisements.length === 0) return null;

    return (
        <div className={`ad-container ad-position-${position}`}>
            {advertisements.map(ad => (
                <div 
                    key={ad.id} 
                    className="advertisement"
                    style={{ backgroundColor: ad.background_color }}
                    onClick={() => handleAdClick(ad)}
                >
                    {ad.translates?.az?.photo && (
                        <img 
                            src={ad.translates.az.photo}
                            alt="Advertisement"
                            className="ad-image"
                        />
                    )}
                </div>
            ))}
        </div>
    );
};

export default AdDisplay;

// İstifadə nümunələri:
// <AdDisplay position="web_top" />
// <AdDisplay position="listing_right" categoryIds={[1, 2, 3]} />
// <AdDisplay position="mobile_top" />
```

## 📊 View Tracking System

### Frontend View Tracking

```javascript
// AdViewTracker.js
class AdViewTracker {
    constructor() {
        this.viewedAds = new Set();
        this.intersectionObserver = null;
        this.initIntersectionObserver();
    }

    initIntersectionObserver() {
        this.intersectionObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
                        const adId = entry.target.dataset.adId;
                        this.trackView(adId);
                    }
                });
            },
            {
                threshold: 0.5,
                rootMargin: '0px'
            }
        );
    }

    trackView(adId) {
        if (this.viewedAds.has(adId)) {
            return; // Artıq baxılıb
        }

        this.viewedAds.add(adId);
        
        // Backend-ə view məlumatını göndər
        fetch(`/api/frontend/advertisements/${adId}/view`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).catch(error => {
            console.error('View tracking failed:', error);
        });
    }

    observeAd(element, adId) {
        element.dataset.adId = adId;
        this.intersectionObserver.observe(element);
    }

    disconnect() {
        if (this.intersectionObserver) {
            this.intersectionObserver.disconnect();
        }
    }
}

export default AdViewTracker;
```

## 🔧 Validation Rules

### Backend Validation

```php
// AdvertisementController.php
public function commonRules(): array
{
    return [
        'position' => [
            'required', 
            'in:' . implode(',', AdvertisementPositionEnum::getValues())
        ],
        'translates' => ['required', 'array'],
        'translates.*.link' => ['nullable', 'url'],
        'translates.*.photo' => [
            'required',
