<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InputRule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function fieldTypes(): BelongsToMany
    {
        return $this->belongsToMany(FieldType::class, 'input_rule_field_types');
    }

    public function fieldRules(): HasMany
    {
        return $this->hasMany(FieldRule::class);
    }
}
