<?php

namespace App\Models;

use App\Enums\TranslationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Log\Traits\Auditable;

class Translation extends Model
{
    use Auditable;

    protected $fillable = [
        'key',
        'locale',
        'value',
        'status',
        'sources',
        'updated_by',
    ];

    protected $casts = [
        'sources' => 'array',
    ];

    public function scopeLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    public function syncStatus(): void
    {
        $this->status = TranslationStatus::fromValue($this->value)->value;
    }
}
