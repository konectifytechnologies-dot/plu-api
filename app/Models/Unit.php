<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Unit extends Model
{
    use HasUlids;

    protected $keyType = 'string'; 
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $fillable = [
        'id',
        'name',
        'bedrooms',
        'rent',
        'property_id',
        'is_deleted'
    ];
    protected function casts(): mixed
    {
        return ['is_deleted' => 'boolean'];
    }
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function readings()
    {
        return $this->hasMany(WaterReading::class);
    }

    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }

    public function tenancy()
    {
        return $this->hasOne(Tenancy::class)
                    ->where('status', 'active');
    } 

}
