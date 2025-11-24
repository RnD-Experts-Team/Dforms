<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Stage extends Model
{
    protected $fillable = [
        'form_version_id',
        'name',
        'is_initial',
        'visibility_condition',
    ];

    protected $casts = [
        'is_initial' => 'boolean',
        'visibility_condition' => 'array',
    ];

    public function formVersion(): BelongsTo
    {
        return $this->belongsTo(FormVersion::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function transitionsFrom(): HasMany
    {
        return $this->hasMany(StageTransition::class, 'from_stage_id');
    }

    public function transitionsTo(): HasMany
    {
        return $this->hasMany(StageTransition::class, 'to_stage_id');
    }

    public function accessRule(): HasOne
    {
        return $this->hasOne(StageAccessRule::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class, 'current_stage_id');
    }
}
