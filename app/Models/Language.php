<?php
namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Log\Traits\Auditable;

class Language extends Model
{
    use SoftDeletes, Auditable;

    protected $guarded = [];

    /** Only Active languages */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', StatusEnum::ACTIVE);
    }

    public function scopeAdminDefault(Builder $q): Builder
    {
        return $q->active()->where('is_default_admin', true);
    }

    public function scopeSiteDefault(Builder $q): Builder
    {
        return $q->active()->where('is_default_site', true);
    }

    public function scopeIsRequired(Builder $q): Builder
    {
        return $q->active()->where('is_required', true);
    }
}
