<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StageTransitionTranslation extends Model
{
    protected $fillable = ['stage_transition_id', 'language_id', 'label'];

    public function stageTransition(): BelongsTo { return $this->belongsTo(StageTransition::class); }
    public function language(): BelongsTo { return $this->belongsTo(Language::class); }
}
