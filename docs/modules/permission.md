# Permission Service Documentation

## 📋 Ümumi Məlumat

Permission Service Laravel 12 layihəsində rol və icazə (permission) idarəetməsini həyata keçirir. Bu service RBAC (Role-Based Access Control) sistemini dəstəkləyir və bütün istifadəçi icazələrini mərkəzləşdirilmiş şəkildə idarə edir.

## 🚀 Xüsusiyyətlər

- ✅ **RBAC Sistemi** - Rol əsaslı çıxış nəzarəti
- ✅ **Dynamic Permissions** - Konfiqurasiya faylından avtomatik permission yaratma
- ✅ **Permission Grouping** - Qruplanmış permission idarəetməsi
- ✅ **Role Management** - Tam rol idarəetməsi
- ✅ **Cache Support** - Performance üçün cache dəstəyi
- ✅ **Sync Operations** - Permissions və roles sync
- ✅ **System Roles** - Sistem rolları qorunması
- ✅ **Permission Comparison** - Rol permissions müqayisəsi

## 📊 Məlumat Bazası Strukturu

### `permissions` cədvəli
```sql
CREATE TABLE permissions (
                             id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                             name VARCHAR(255) NOT NULL,
                             guard_name VARCHAR(255) DEFAULT 'api',
                             created_at TIMESTAMP NULL,
                             updated_at TIMESTAMP NULL
);
```

### `roles` cədvəli
```sql
CREATE TABLE roles (
                       id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                       name VARCHAR(255) NOT NULL,
                       group_name VARCHAR(255) DEFAULT 'admin',
                       guard_name VARCHAR(255) DEFAULT 'api',
                       is_system BOOLEAN DEFAULT FALSE,
                       created_at TIMESTAMP NULL,
                       updated_at TIMESTAMP NULL
);
```

### `role_has_permissions` cədvəli
```sql
CREATE TABLE role_has_permissions (
                                      permission_id BIGINT UNSIGNED NOT NULL,
                                      role_id BIGINT UNSIGNED NOT NULL,
                                      PRIMARY KEY (permission_id, role_id),
                                      FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
                                      FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

### Users cədvəlində əlavə
```sql
ALTER TABLE users ADD COLUMN role_id BIGINT UNSIGNED DEFAULT 1 AFTER id;
```

## 🔗 API Endpoints

### Permission Management

| HTTP Method | Endpoint | Açıqlama | Permission |
|-------------|----------|----------|------------|
| `GET` | `/api/admin/permissions` | Rol siyahısı | `permission_read` |
| `POST` | `/api/admin/permissions` | Yeni rol yaratma | `permission_create` |
| `GET` | `/api/admin/permissions/{id}` | Rol ətraflı məlumat | `permission_read` |
| `PUT` | `/api/admin/permissions/{id}` | Rol məlumat yeniləmə | `permission_update` |
| `DELETE` | `/api/admin/permissions/{id}` | Rol silmə | `permission_delete` |
| `GET` | `/api/admin/permissions/{id}/grouped` | Qruplanmış permissions | `permission_read` |
| `POST` | `/api/admin/permissions/{id}/updatePermissions` | Permission update | `permission_create` |

## ⚙️ Konfiqurasiya

### config/permissions.php

```php
<?php

$permissionArr = [
    "user" => [
        "user_create",
        "user_update", 
        "user_delete",
        "user_read",
        "user_status",
        "user_terminal",
    ],
    "language" => [
        "language_create",
        "language_update",
        "language_delete", 
        "language_read",
        "language_status",
    ],
    "translation" => [
        "translation_create",
        "translation_update",
        "translation_delete",
        "translation_read",
    ],
    "page" => [
        "page_create",
        "page_update",
        "page_delete",
        "page_read", 
        "page_status",
    ],
    "seo" => [
        "seo_create",
        "seo_update",
        "seo_delete",
        "seo_read",
        "seo_status",
    ],
    "activity_log" => [
        "activity_log_create",
        "activity_log_update",
        "activity_log_delete",
        "activity_log_read",
        "activity_log_status",
    ],
    "permission" => [
        "permission_create",
        "permission_update",
        "permission_delete",
        "permission_read",
        "permission_status",
    ],
    "notification" => [
        "notification_create",
        "notification_update",
        "notification_delete",
        "notification_read",
        "notification_status",
    ],
    "complaint" => [
        "complaint_create",
        "complaint_update",
        "complaint_delete",
        "complaint_read",
        "complaint_status",
        "complaint_reply",
    ],
    "expense_category" => [
        "expense_category_create",
        "expense_category_update",
        "expense_category_delete",
        "expense_category_read",
        "expense_category_status",
    ],
    "income_category" => [
        "income_category_create",
        "income_category_update",
        "income_category_delete",
        "income_category_read",
        "income_category_status",
    ],
    "income" => [
        "income_create",
        "income_update",
        "income_delete",
        "income_read",
        "income_status",
    ],
    "config" => [
        "config_create",
        "config_update",
        "config_delete",
        "config_read",
        "config_status",
        "config_monitoring",
    ],
    "comment" => [
        "comment_create",
        "comment_update",
        "comment_delete",
        "comment_read",
        "comment_status",
    ],
    "messaging" => [
        "messaging_create",
        "messaging_update",
        "messaging_delete",
        "messaging_read",
        "messaging_status",
        "messaging_view_message",
    ],
    "mail_template" => [
        "mail_template_create",
        "mail_template_update",
        "mail_template_delete",
        "mail_template_read",
        "mail_template_status",
    ],
    "mail_list" => [
        "mail_list_create",
        "mail_list_update",
        "mail_list_delete",
        "mail_list_read",
        "mail_list_status",
    ],
    "mail_campaign" => [
        "mail_campaign_create",
        "mail_campaign_update",
        "mail_campaign_delete",
        "mail_campaign_read",
        "mail_campaign_status",
    ],
    "mail_log" => [
        "mail_log_create",
        "mail_log_update",
        "mail_log_delete",
        "mail_log_read",
        "mail_log_status",
    ],
    "blocked_credential" => [
        "blocked_credential_create",
        "blocked_credential_update",
        "blocked_credential_delete",
        "blocked_credential_read",
        "blocked_credential_status",
    ],
    "category" => [
        "category_create",
        "category_update",
        "category_delete",
        "category_read",
        "category_status",
    ],
    "attribute" => [
        "attribute_create",
        "attribute_update",
        "attribute_delete",
        "attribute_read",
        "attribute_status",
    ],
    "listing" => [
        "listing_create",
        "listing_update",
        "listing_delete",
        "listing_read",
        "listing_status",
    ],
    "terms" => [
        "terms_create",
        "terms_update",
        "terms_delete",
        "terms_read",
        "terms_status",
    ],
    "voting_system" => [
        "voting_system_create",
        "voting_system_update",
        "voting_system_delete",
        "voting_system_read",
        "voting_system_status",
    ],
    "payment_service" => [
        "payment_service_create",
        "payment_service_update",
        "payment_service_delete",
        "payment_service_read",
        "payment_service_status",
    ],
    "company_type" => [
        "company_type_create",
        "company_type_update",
        "company_type_delete",
        "company_type_read",
        "company_type_status",
    ],
    "company_package" => [
        "company_package_create",
        "company_package_update",
        "company_package_delete",
        "company_package_read",
        "company_package_status",
    ],
    "company_benefit" => [
        "company_benefit_create",
        "company_benefit_update",
        "company_benefit_delete",
        "company_benefit_read",
        "company_benefit_status",
    ],
    "company" => [
        "company_create",
        "company_update",
        "company_delete",
        "company_read",
        "company_status",
    ],
    "story" => [
        "story_create",
        "story_update",
        "story_delete",
        "story_read",
        "story_status",
    ],
    "section" => [
        "section_create",
        "section_update",
        "section_delete",
        "section_read",
        "section_status",
    ],
    "payment" => [
        "payment_create",
        "payment_update",
        "payment_delete",
        "payment_read",
        "payment_status",
    ],
    "country" => [
        "country_create",
        "country_update",
        "country_delete",
        "country_read",
        "country_status",
    ],
    "city" => [
        "city_create",
        "city_update",
        "city_delete",
        "city_read",
        "city_status",
    ],
    "region" => [
        "region_create",
        "region_update",
        "region_delete",
        "region_read",
        "region_status",
    ],
    "subway" => [
        "subway_create",
        "subway_update",
        "subway_delete",
        "subway_read",
        "subway_status",
    ],
    "advertisement" => [
        "advertisement_create",
        "advertisement_update",
        "advertisement_delete",
        "advertisement_read",
        "advertisement_status",
    ],
    "referral" => [
        "referral_create",
        "referral_update",
        "referral_delete",
        "referral_read",
        "referral_status",
    ],
    "expense" => [
        "expense_create",
        "expense_update",
        "expense_delete",
        "expense_read",
        "expense_status",
    ],
    "currency" => [
        "currency_create",
        "currency_update",
        "currency_delete",
        "currency_read",
        "currency_status",
    ],
    "company_vote" => [
        "company_vote_create",
        "company_vote_update",
        "company_vote_delete",
        "company_vote_read",
        "company_vote_status",
    ],
];

return [
    "permissions" => $permissionArr,
    "roles" => [
        "user" => [], // Sadə istifadəçi rolları
        "admin" => $permissionArr, // Admin bütün permissions alır
    ]
];
```

## 🎯 Permission Qrupları və Actions

Sistemdə 33 əsas permission qrupu və hərdə 4-6 action mövcuddur:

### Standard Actions
- **create** - Yeni record yaratma icazəsi
- **read** - Məlumatları oxuma icazəsi
- **update** - Məlumatları yeniləmə icazəsi
- **delete** - Məlumatları silmə icazəsi
- **status** - Status dəyişmə icazəsi

### Xüsusi Actions
- **user_terminal** - İstifadəçi terminal girişi
- **complaint_reply** - Şikayətə cavab vermə
- **config_monitoring** - Konfiqurasiya monitorinqi
- **messaging_view_message** - Mesaj görüntüləmə

### Permission Qrupları

| Qrup | Açıqlama | Xüsusi Actions |
|------|----------|----------------|
| `user` | İstifadəçi idarəetməsi | `user_terminal` |
| `language` | Dil idarəetməsi | - |
| `translation` | Tərcümə idarəetməsi | - |
| `page` | Səhifə idarəetməsi | - |
| `seo` | SEO idarəetməsi | - |
| `activity_log` | Fəaliyyət logları | - |
| `permission` | İcazə idarəetməsi | - |
| `notification` | Bildiriş idarəetməsi | - |
| `complaint` | Şikayət idarəetməsi | `complaint_reply` |
| `expense_category` | Xərc kateqoriyaları | - |
| `income_category` | Gəlir kateqoriyaları | - |
| `income` | Gəlir idarəetməsi | - |
| `config` | Konfiqurasiya | `config_monitoring` |
| `comment` | Şərh idarəetməsi | - |
| `messaging` | Mesajlaşma sistemi | `messaging_view_message` |
| `mail_template` | Email şablonları | - |
| `mail_list` | Email siyahıları | - |
| `mail_campaign` | Email kampaniyaları | - |
| `mail_log` | Email logları | - |
| `blocked_credential` | Bloklanmış məlumatlar | - |
| `category` | Kateqoriya idarəetməsi | - |
| `attribute` | Atribut idarəetməsi | - |
| `listing` | Elan idarəetməsi | - |
| `terms` | Şərtlər idarəetməsi | - |
| `voting_system` | Səsvermə sistemi | - |
| `payment_service` | Ödəniş xidmətləri | - |
| `company_type` | Şirkət növləri | - |
| `company_package` | Şirkət paketləri | - |
| `company_benefit` | Şirkət imtiyazları | - |
| `company` | Şirkət idarəetməsi | - |
| `story` | Hekayə idarəetməsi | - |
| `section` | Bölmə idarəetməsi | - |
| `payment` | Ödəniş idarəetməsi | - |
| `country` | Ölkə idarəetməsi | - |
| `city` | Şəhər idarəetməsi | - |
| `region` | Region idarəetməsi | - |
| `subway` | Metro idarəetməsi | - |
| `advertisement` | Reklam idarəetməsi | - |
| `referral` | Referral idarəetməsi | - |
| `expense` | Xərc idarəetməsi | - |
| `currency` | Valyuta idarəetməsi | - |
| `company_vote` | Şirkət səsverməsi | - |

### Permission İdarəetməsi

```javascript
// Rol siyahısını əldə etmə
const getRoles = async () => {
    const response = await fetch('/api/admin/permissions', {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    
    const roles = await response.json();
    return roles;
};

// Rolun permissions-lərini əldə etmə
const getRolePermissions = async (roleId) => {
    const response = await fetch(`/api/admin/permissions/${roleId}/grouped`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    
    const permissions = await response.json();
    return permissions;
};

// Permission update
const updateRolePermissions = async (roleId, permissions) => {
    const response = await fetch(`/api/admin/permissions/${roleId}/updatePermissions`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ permissions })
    });
    
    const result = await response.json();
    return result;
};
```

### React Permission Component

```jsx
// PermissionMatrix.jsx
import React, { useState, useEffect } from 'react';

const PermissionMatrix = ({ roleId }) => {
    const [permissions, setPermissions] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchPermissions();
    }, [roleId]);

    const fetchPermissions = async () => {
        try {
            const response = await fetch(`/api/admin/permissions/${roleId}/grouped`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                setPermissions(data);
            }
        } catch (error) {
            console.error('Permissions yüklənmədi:', error);
        } finally {
            setLoading(false);
        }
    };

    const handlePermissionChange = (group, action, value) => {
        setPermissions(prev => ({
            ...prev,
            permissions: {
                ...prev.permissions,
                [group]: {
                    ...prev.permissions[group],
                    permissions: {
                        ...prev.permissions[group].permissions,
                        [action]: {
                            ...prev.permissions[group].permissions[action],
                            value: value
                        }
                    }
                }
            }
        }));
    };

    const savePermissions = async () => {
        try {
            const response = await fetch(`/api/admin/permissions/${roleId}/updatePermissions`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ permissions })
            });

            if (response.ok) {
                alert('Permissions yeniləndi!');
            }
        } catch (error) {
            console.error('Permissions yenilənmədi:', error);
        }
    };

    if (loading) return <div>Yüklənir...</div>;

    return (
        <div className="permission-matrix">
            <h3>Role: {permissions.role}</h3>
            
            {Object.entries(permissions.permissions).map(([groupName, group]) => (
                <div key={groupName} className="permission-group">
                    <h4>{group.name}</h4>
                    
                    <div className="permissions-grid">
                        {Object.entries(group.permissions).map(([actionName, permission]) => (
                            <label key={actionName} className="permission-item">
                                <input
                                    type="checkbox" 
                                    checked={permission.value}
                                    onChange={(e) => handlePermissionChange(
                                        groupName, 
                                        actionName, 
                                        e.target.checked
                                    )}
                                />
                                {permission.name}
                            </label>
                        ))}
                    </div>
                </div>
            ))}
            
            <button onClick={savePermissions} className="btn-save">
                Yadda saxla
            </button>
        </div>
    );
};

export default PermissionMatrix;
```

## 🛠 Service Metodları

### PermissionService Class

```php
namespace App\Services\Module;

class PermissionService extends BaseCrudService
{
    /**
     * Qruplanmış permissions əldə edir
     * 
     * @param int $id Role ID
     * @return array
     */
    public function fetchGroupedPermissions($id): array

    /**
     * Rolun permissions-lərini yeniləyir
     * 
     * @param int $id Role ID
     * @param array $request Permission məlumatları
     * @return bool
     */
    public function updatePermissions($id, $request): bool

    /**
     * Konfiqurasiyadan permissions-ləri sync edir
     * 
     * @throws Exception
     */
    public function syncPermissions(): void

    /**
     * Konfiqurasiyadan roles-ləri sync edir
     * 
     * @throws Exception  
     */
    public function syncRoles(): void

    /**
     * Bütün permissions-ləri qaytarır
     * 
     * @return array
     */
    public function getAllPermissions(): array

    /**
     * Rolun permissions-lərini qaytarır
     * 
     * @param string $roleName
     * @return array
     */
    public function getRolePermissions($roleName): array

    /**
     * İstifadəçinin permission-ə sahib olub-olmadığını yoxlayır
     * 
     * @param User $user
     * @param string $permission
     * @return bool
     */
    public function hasPermission($user, $permission): bool
}
```

## 🏗️ Repository Metodları

### PermissionRepository Class

```php
namespace App\Repositories\Module;

class PermissionRepository extends BaseRepository
{
    /**
     * Sistem rollarını silməyə icazə vermir
     */
    public function delete(int $id): bool

    /**
     * Permissions-ləri qruplayır
     * 
     * @return array
     */
    public function getGroupedPermissions(): array

    /**
     * Rol və permissions müqayisəsi
     * 
     * @param int $roleId
     * @return array
     */
    public function getPermissionComparison($roleId): array

    /**
     * Rolun permissions-lərini yeniləyir
     * 
     * @param int $roleId
     * @param array $permissions
     * @return bool
     * @throws Exception
     */
    public function updatePermissions($roleId, $permissions): bool
}
```

## 🔄 Seeding və Sync

### Database Seeding

```php
// PermissionSeeder.php
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->permissionService->syncPermissions();
        $this->permissionService->syncRoles();
    }
}

// Terminal-dən çalıştırma
php artisan db:seed --class=PermissionSeeder
```

### Artisan Commands

```bash
# Permission və role sync
php artisan permission:sync

# Cache təmizləmə
php artisan cache:clear

# Config cache
php artisan config:cache
```

## 🔒 Permission Yoxlaması

### Controller-də İstifadə

```php
// ApiController base class-ında
protected function authorizeAction(string $action): bool
{
    $permission = $this->getPermissionName($action);
    return auth()->user()?->hasPermission($permission) ?? false;
}

private function getPermissionName(string $action): string
{
    return $this->resource . '_' . $action;
}

// İstifadə nümunəsi
public function index(): JsonResponse
{
    if (!$this->authorizeAction('read')) {
        return response()->json(['message' => $this->forbiddenMessage], 403);
    }
    
    // Controller logic...
}
```

### Middleware İstifadəsi

```php
// Permission middleware yaratma
class CheckPermission
{
    public function handle($request, Closure $next, string $permission)
    {
        if (!auth()->user()?->hasPermission($permission)) {
            return response()->json(['message' => 'Bu əməliyyat üçün icazəniz yoxdur'], 403);
        }
        
        return $next($request);
    }
}

// Route-da istifadə
Route::get('/admin/users', [UserController::class, 'index'])
    ->middleware('permission:user_read');
```

### Blade Template-də

```php
// İstifadəçi permission yoxlaması
@can('user_create')
    <button class="btn btn-primary">Yeni İstifadəçi</button>
@endcan

// Rol yoxlaması  
@role('admin')
    <div class="admin-panel">
        Admin paneli
    </div>
@endrole
```

## 🎯 Model Metodları

### User Model

```php
// User.php - HasPermissions trait
trait HasPermissions
{
    /**
     * Permission yoxlaması
     */
    public function hasPermission(string $permission): bool
    {
        return $this->getAllPermissions()->contains('name', $permission);
    }

    /**
     * Bütün permissions (cache ilə)
     */
    public function getAllPermissions(): Collection
    {
        $cacheKey = 'user_permissions_' . $this->id;
        
        return Cache::remember($cacheKey, now()->addHours(24), function () {
            return $this->role->permissions;
        });
    }

    /**
     * Permission cache təmizləmə
     */
    public function forgetCachedPermissions(): void
    {
        Cache::forget('user_permissions_' . $this->id);
    }

    /**
     * Rol yoxlaması
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role->group_name === $roleName;
    }
}
```

### Role Model

```php
// Role.php
class Role extends Model
{
    /**
     * Permissions sync (cache təmizləmə ilə)
     */
    public function syncPermissions($permissions): array
    {
        $result = $this->permissions()->sync($permissions);
        $this->clearUsersPermissionCache();
        return $result;
    }

    /**
     * Permission əlavə etmə
     */
    public function givePermissionTo($permission): array
    {
        $result = $this->permissions()->syncWithoutDetaching($permission);
        $this->clearUsersPermissionCache();
        return $result;
    }

    /**
     * Permission götürmə
     */
    public function revokePermissionTo($permission): int
    {
        $result = $this->permissions()->detach($permission);
        $this->clearUsersPermissionCache();
        return $result;
    }

    /**
     * Bütün istifadəçilərin cache-ini təmizləmə
     */
    protected function clearUsersPermissionCache(): void
    {
        $this->users->each(function ($user) {
            $user->forgetCachedPermissions();
        });
    }

    /**
     * Permission yoxlaması
     */
    public function hasPermissionTo($permission): bool
    {
        return $this->permissions->contains('name', $permission);
    }
}
```

## 🔍 Permission Filtering

### BaseFilter Class

Permission filter sistemi üçün əsas filter class:

```php
// BaseFilter.php
abstract class BaseFilter implements FilterInterface
{
    protected array $filters = [];
    protected string $defaultSortColumn = 'id';
    protected string $defaultSortDirection = 'desc';

    public function apply(Builder $query): Builder
    {
        // Filter tətbiqi
        foreach ($this->getFilters() as $filter => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $method = 'filter' . Str::studly($filter);

            if (method_exists($this, $method)) {
                $query = $this->$method($query, $value);
            }
        }

        // Sorting
        return $this->applySorting($query);
    }

    // Ümumi filter metodları
    protected function filterStatus(Builder $query, $value): Builder
    {
        return $query->where('status', $value);
    }

    protected function filterSearch(Builder $query, string $value): Builder
    {
        $searchableFields = $this->getSearchableFields();
        
        return $query->where(function (Builder $q) use ($value, $searchableFields) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'like', '%' . $value . '%');
            }
        });
    }
}
```

## 🚀 Performance Optimizasiyası

### Cache Strategiyası

```php
// Permission cache
Cache::remember("user_permissions_{$userId}", now()->addHours(24), function() use ($userId) {
    return User::find($userId)->role->permissions;
});

// Role permissions cache
Cache::remember("role_permissions_{$roleId}", now()->addHours(12), function() use ($roleId) {
    return Role::with('permissions')->find($roleId);
});

// Grouped permissions cache
Cache::remember('grouped_permissions', now()->addHours(6), function() {
    return $this->repository->getGroupedPermissions();
});
```

### Database İndekslər

```sql
-- Performance üçün indekslər
CREATE INDEX idx_role_has_permissions_role_id ON role_has_permissions(role_id);
CREATE INDEX idx_role_has_permissions_permission_id ON role_has_permissions(permission_id);
CREATE INDEX idx_users_role_id ON users(role_id);
CREATE INDEX idx_permissions_name ON permissions(name);
CREATE INDEX idx_roles_group_name ON roles(group_name);
```

## 🧪 Testing

### Unit Tests

```php
// PermissionServiceTest.php
class PermissionServiceTest extends TestCase
{
    public function test_can_sync_permissions()
    {
        $this->permissionService->syncPermissions();
        
        $this->assertDatabaseHas('permissions', [
            'name' => 'user_read'
        ]);
    }

    public function test_can_sync_roles()
    {
        $this->permissionService->syncRoles();
        
        $this->assertDatabaseHas('roles', [
            'name' => 'Admin',
            'group_name' => 'admin'
        ]);
    }

    public function test_user_has_permission()
    {
        $user = User::factory()->create(['role_id' => 1]);
        
        $this->assertTrue($user->hasPermission('user_read'));
        $this->assertFalse($user->hasPermission('nonexistent_permission'));
    }

    public function test_role_permission_sync()
    {
        $role = Role::factory()->create();
        $permissions = Permission::factory()->count(3)->create();
        
        $result = $role->syncPermissions($permissions->pluck('id')->toArray());
        
        $this->assertCount(3, $role->fresh()->permissions);
    }
}
```

### Feature Tests

```php
// PermissionControllerTest.php
class PermissionControllerTest extends TestCase
{
    public function test_can_get_grouped_permissions()
    {
        $admin = User::factory()->admin()->create();
        $role = Role::factory()->create();

        $response = $this->actingAs($admin)
            ->getJson("/api/admin/permissions/{$role->id}/grouped");

        $response->assertOk()
            ->assertJsonStructure([
                'role',
                'permissions' => [
                    '*' => [
                        'name',
                        'permissions' => [
                            '*' => [
                                'name',
                                'value'
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_can_update_permissions()
    {
        $admin = User::factory()->admin()->create();
        $role = Role::factory()->create();

        $permissionData = [
            'permissions' => [
                'user' => [
                    'permissions' => [
                        'read' => ['value' => true],
                        'create' => ['value' => false]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($admin)
            ->postJson("/api/admin/permissions/{$role->id}/updatePermissions", $permissionData);

        $response->assertOk();
        
        $this->assertTrue($role->fresh()->hasPermissionTo('user_read'));
        $this->assertFalse($role->fresh()->hasPermissionTo('user_create'));
    }
}
```

## 📋 TODO və Gələcək Təkmilləşdirmələr

- [ ] **Dynamic permission creation** UI-dan permission yaratma
- [ ] **Permission inheritance** parent-child permission sistemi
- [ ] **Time-based permissions** müvəqqəti icazələr
- [ ] **Resource-based permissions** ID əsaslı icazələr
- [ ] **Permission audit log** icazə dəyişiklik tarixçəsi
- [ ] **GraphQL support** GraphQL API dəstəyi
- [ ] **Permission export/import** Excel formatında
- [ ] **Advanced caching** Redis cluster dəstəyi

## ⚠️ Təhlükəsizlik Qeydləri

### Best Practices

1. **Sistem rollarını silməyin** - `is_system` flagı olanlar
2. **Cache invalidation** - Permission dəyişikliklərində cache təmizləyin
3. **Permission naming** - Aydın və strukturlaşdırılmış adlar istifadə edin
4. **Regular audits** - Permission assignments-ləri müntəzəm yoxlayın
5. **Least privilege** - Minimum lazım olan permissions verin

### Security Checklist

- [ ] Permission names are descriptive and follow naming convention
- [ ] System roles are protected from deletion
- [ ] Cache is cleared after permission changes
- [ ] Permissions are checked in all sensitive operations
- [ ] Audit logs are maintained for permission changes
- [ ] Regular permission reviews are conducted

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
