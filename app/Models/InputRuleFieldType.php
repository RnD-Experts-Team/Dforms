<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InputRuleFieldType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'input_rule_id',
        'field_type_id',
    ];

    public function inputRule(): BelongsTo
    {
        return $this->belongsTo(InputRule::class);
    }

    public function fieldType(): BelongsTo
    {
        return $this->belongsTo(FieldType::class);
    }
}
