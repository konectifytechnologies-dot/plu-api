<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class WaterReading extends Model
{
    //
    use HasUlids;

    protected $keyType = 'string'; 
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $fillable = [
        'id',
        'property_id',
        'unit_id',
        'year',
        'month',
        'previous_reading',
        'current_reading',
        'units_consumed',
        'amount'
    ];

    
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    protected function casts(): array
    {
        return ['previous_reading'=>'integer', 'current_reading'=>'integer', 'units_consumed'=>'integer', 'amount'=>'integer'];
    }
    
}
