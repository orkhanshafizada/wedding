<?php
namespace Modules\Log\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'activity_logs';

    protected $fillable = [
        'actor_id',
        'actor_type',
        'subject_id',
        'subject_type',
        'action',
        'module',
        'route',
        'url',
        'method',
        'status_code',
        'ip',
        'user_agent',
        'request_id',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function actor(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'actor_type', 'actor_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'subject_type', 'subject_id');
    }

    public function changes(): HasMany
    {
        return $this->hasMany(ActivityLogChange::class, 'activity_log_id');
    }
}
