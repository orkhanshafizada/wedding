<?php

namespace Modules\Form\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Log\Traits\Auditable;

class FormLabelTranslation extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'form_label_id',
        'locale',
        'name',
        'information',
    ];

    public function formLabel(): BelongsTo
    {
        return $this->belongsTo(FormLabel::class);
    }
}
