<?php

namespace Modules\Form\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Log\Traits\Auditable;

class FormTextTranslation extends Model
{
    use Auditable;

    /** Mass-assignable sahələr */
    protected $fillable = [
        'form_text_id',
        'locale',
        'header_text',
        'success_text',
        'email_text',
    ];

    /** Ana form text əlaqəsi */
    public function formText(): BelongsTo
    {
        return $this->belongsTo(FormText::class);
    }
}
