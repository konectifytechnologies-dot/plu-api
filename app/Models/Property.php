<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Property extends Model
{
    use HasUlids;

    protected $keyType = 'string'; 
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $fillable = [
        'id',
        'picture',
        'name',
        'number_of_units',
        'location',
        'water_unit_cost',
        'property_type',
        'has_service_charge',
        'service_charge',
        'is_deleted'
    ];

    protected function casts(): mixed
    {
        return ['is_deleted' => 'boolean', 'has_service_charge'=>'boolean'];
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function tenancies()
    {
        return $this->hasMany(Tenancy::class);
    }

    public function readings()
    {
        return $this->hasMany(WaterReading::class);
    }

    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'property_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function landlord()
    {
        return $this->belongsToMany(User::class, 'property_users')
            ->wherePivot('role', 'landlord');
    }

    public function agent()
    {
        return $this->belongsToMany(User::class, 'property_users')
            ->wherePivot('role', 'agent');
    }

    /*public function getLandlordAttribute()
    {
        return $this->landlord()->first();
    }

    public function getAgentAttribute()
    {
        return $this->agent()->first();
    }*/
    
}
