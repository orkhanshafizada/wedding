<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Log\Traits\Auditable;

class Setting extends Model
{
    use Auditable;
    protected $fillable = ['group','key','value'];
    protected $casts = ['value' => 'array'];
}
