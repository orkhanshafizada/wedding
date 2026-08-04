<?php
namespace Modules\Log\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLogChange extends Model
{
    public $timestamps = false;

    protected $table = 'activity_log_changes';

    protected $fillable = [
        'activity_log_id',
        'field',
        'old_value',
        'new_value',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function activityLog(): BelongsTo
    {
        return $this->belongsTo(ActivityLog::class, 'activity_log_id');
    }
}
