<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyUser extends Model
{
     protected $keyType = 'string'; 
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $fillable = [
        'id',
        'property_id',
        'user_id',
        'role',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Role helpers
     */
    public function isLandlord(): bool
    {
        return $this->role === 'landlord';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }
}
