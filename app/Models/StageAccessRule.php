<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StageAccessRule extends Model
{
    protected $fillable = [
        'stage_id',
        'allowed_users',
        'allowed_roles',
        'allowed_permissions',
        'allow_authenticated_users',
        'email_field_id',
    ];

    protected $casts = [
        'allowed_users' => 'array',
        'allowed_roles' => 'array',
        'allowed_permissions' => 'array',
        'allow_authenticated_users' => 'boolean',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function emailField(): BelongsTo
    {
        return $this->belongsTo(Field::class, 'email_field_id');
    }
}
