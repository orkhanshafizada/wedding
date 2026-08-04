<?php
namespace Modules\Log\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminSession extends Model
{
    protected $table = 'admin_sessions';

    protected $fillable = [
        'user_id',
        'guard',
        'session_id',
        'ip',
        'user_agent',
        'device_type',
        'device_brand',
        'device_model',
        'os',
        'os_version',
        'browser',
        'browser_version',
        'login_at',
        'logout_at',
        'last_activity_at',
        'is_successful',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'is_successful' => 'bool',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
