<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Entry extends Model
{
    protected $fillable = [
        'form_version_id',
        'current_stage_id',
        'public_identifier',
        'is_complete',
        'is_considered',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
        'is_considered' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entry) {
            if (empty($entry->public_identifier)) {
                $entry->public_identifier = (string) Str::uuid();
            }
        });
    }

    public function formVersion(): BelongsTo
    {
        return $this->belongsTo(FormVersion::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'current_stage_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(EntryValue::class);
    }
}
