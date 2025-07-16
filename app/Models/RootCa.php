<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RootCa extends Model
{
    // Fillable fields for mass-assignment
    protected $fillable = [
        'name',
        'domain',
        'description',
        'private_key',
        'public_key',
        'certificate',
        'passphrase',
    ];

    // Encrypt sensitive fields automatically
    protected $casts = [
        'private_key' => 'encrypted',
        'passphrase'  => 'encrypted',
        'valid_from'  => 'datetime',
        'valid_to'    => 'datetime',
    ];

    // Relationships
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
