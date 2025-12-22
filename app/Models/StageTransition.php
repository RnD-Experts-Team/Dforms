<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StageTransition extends Model
{
    protected $fillable = [
        'form_version_id',
        'from_stage_id',
        'to_stage_id',
        'to_complete',
        'label',
        'condition',
    ];

    protected $casts = [
        'to_complete' => 'boolean',
        'condition' => 'array',
    ];

    public function formVersion(): BelongsTo
    {
        return $this->belongsTo(FormVersion::class);
    }

    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'from_stage_id');
    }

    public function toStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'to_stage_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(StageTransitionAction::class);
    }

public function translations(): HasMany
{
    return $this->hasMany(StageTransitionTranslation::class);
}
}
