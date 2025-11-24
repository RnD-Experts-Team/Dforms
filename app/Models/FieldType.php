<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FieldType extends Model
{
    protected $fillable = [
        'name',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(Field::class);
    }

    public function fieldTypeFilters(): HasMany
    {
        return $this->hasMany(FieldTypeFilter::class);
    }

    public function inputRules(): BelongsToMany
    {
        return $this->belongsToMany(InputRule::class, 'input_rule_field_types');
    }
}
