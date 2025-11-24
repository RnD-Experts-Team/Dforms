<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Action extends Model
{
    protected $fillable = [
        'name',
        'props_description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function stageTransitionActions(): HasMany
    {
        return $this->hasMany(StageTransitionAction::class);
    }
}
