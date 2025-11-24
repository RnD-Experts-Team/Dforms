<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    protected $fillable = [
        'section_id',
        'field_type_id',
        'label',
        'placeholder',
        'helper_text',
        'default_value',
        'visibility_condition',
    ];

    protected $casts = [
        'visibility_condition' => 'array',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function fieldType(): BelongsTo
    {
        return $this->belongsTo(FieldType::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(FieldRule::class);
    }

    public function entryValues(): HasMany
    {
        return $this->hasMany(EntryValue::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FieldTranslation::class);
    }
}
