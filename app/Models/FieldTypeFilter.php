<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldTypeFilter extends Model
{
    protected $fillable = [
        'field_type_id',
        'filter_method_description',
    ];

    public function fieldType(): BelongsTo
    {
        return $this->belongsTo(FieldType::class);
    }
}
