<?php

namespace Modules\MainPage\Models;

use App\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Log\Traits\Auditable;

class MainPageSectionTranslation extends Model
{
    use Auditable;

    protected $fillable = [
        'main_page_section_id',
        'language_id',
        'title',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(MainPageSection::class, 'main_page_section_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
