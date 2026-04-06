<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuleGroup extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'parent_id'];

    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class);
    }

    public function activeRules(): HasMany
    {
        return $this->hasMany(Rule::class)->where('is_active', true);
    }

    public function children(): HasMany
    {
        return $this->hasMany(RuleGroup::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RuleGroup::class, 'parent_id');
    }
}
