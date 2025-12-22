<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SectionTranslation extends Model
{
    protected $fillable = ['section_id', 'language_id', 'name'];

    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function language(): BelongsTo { return $this->belongsTo(Language::class); }
}
