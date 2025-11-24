<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StageTransitionAction extends Model
{
    protected $fillable = [
        'stage_transition_id',
        'action_id',
        'action_props',
    ];

    protected $casts = [
        'action_props' => 'array',
    ];

    public function stageTransition(): BelongsTo
    {
        return $this->belongsTo(StageTransition::class);
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }
}
