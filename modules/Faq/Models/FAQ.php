<?php

namespace Modules\FAQ\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Log\Traits\Auditable;

class FAQ extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'faqs';

    protected $fillable = [
        'menu_id',
        'question',
        'answer',
        'sort_order',
        'is_active',
        'view_count',
    ];

    protected $casts = [
        'question' => 'array',
        'answer' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'view_count' => 'integer',
    ];

    // Relationships
    public function menu()
    {
        return $this->belongsTo(\Modules\Menu\Models\Menu::class);
    }

    // Accessors
    public function getQuestionAttribute($value): ?string
    {
        $questions = json_decode($value, true);
        $locale = app()->getLocale();
        return $questions[$locale] ?? $questions['az'] ?? null;
    }

    public function getAnswerAttribute($value): ?string
    {
        $answers = json_decode($value, true);
        $locale = app()->getLocale();
        return $answers[$locale] ?? $answers['az'] ?? null;
    }

    public function getTranslatedQuestion(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $questions = is_array($this->attributes['question'] ?? null)
            ? $this->attributes['question']
            : json_decode($this->attributes['question'] ?? '{}', true);

        return $questions[$locale] ?? $questions['az'] ?? null;
    }

    public function getTranslatedAnswer(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $answers = is_array($this->attributes['answer'] ?? null)
            ? $this->attributes['answer']
            : json_decode($this->attributes['answer'] ?? '{}', true);

        return $answers[$locale] ?? $answers['az'] ?? null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // Methods
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}

