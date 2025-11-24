<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function formVersionTranslations(): HasMany
    {
        return $this->hasMany(FormVersionTranslation::class);
    }

    public function fieldTranslations(): HasMany
    {
        return $this->hasMany(FieldTranslation::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'default_language_id');
    }
}
