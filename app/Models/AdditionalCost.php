<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class AdditionalCost extends Model
{
    use HasUlids;

    protected $keyType = 'string'; 
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $fillable = [
        'id',
        'title',
        'cost',
        'property_id'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

}
