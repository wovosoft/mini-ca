<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'root_ca_id',
        'name',
        'domain',
        'private_key',
        'public_key',
        'certificate',
        'passphrase',
        'expires_at',
        'valid_from',
    ];

    protected $hidden = [
        'passphrase',
    ];

    protected $casts = [
        'private_key' => 'encrypted',
        'passphrase'  => 'encrypted',
        'expires_at'  => 'datetime',
        'valid_from'  => 'datetime',
    ];

    public function rootCa(): BelongsTo
    {
        return $this->belongsTo(RootCa::class);
    }
}
