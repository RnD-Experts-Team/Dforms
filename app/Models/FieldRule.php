<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldRule extends Model
{
    protected $fillable = [
        'field_id',
        'input_rule_id',
        'rule_props',
        'rule_condition',
    ];

    protected $casts = [
        'rule_props' => 'array',
        'rule_condition' => 'array',
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function inputRule(): BelongsTo
    {
        return $this->belongsTo(InputRule::class);
    }
}
