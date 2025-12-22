<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StageTranslation extends Model
{
    protected $fillable = ['stage_id', 'language_id', 'name'];

    public function stage(): BelongsTo { return $this->belongsTo(Stage::class); }
    public function language(): BelongsTo { return $this->belongsTo(Language::class); }
}

