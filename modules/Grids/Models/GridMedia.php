<?php

namespace Modules\Grids\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Modules\Log\Traits\Auditable;

class GridMedia extends Model
{
    use Auditable;

    protected $table = 'grid_media';

    protected $fillable = [
        'grid_id',
        'type',
        'path',
        'original_name',
        'sort_order',
        'is_main',
    ];

    protected $casts = [
        'grid_id' => 'integer',
        'sort_order' => 'integer',
        'is_main' => 'boolean',
    ];

    protected $appends = [
        'url',
    ];

    public function grid(): BelongsTo
    {
        return $this->belongsTo(Grid::class, 'grid_id');
    }

    public function getUrlAttribute(): string
    {
        $path = (string) ($this->path ?? '');

        if ($path === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public function getFullPathAttribute(): string
    {
        return Storage::disk('public')->path($this->path);
    }
}
