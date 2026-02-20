<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasUlids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $keyType = 'string'; 
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $fillable = [
        'id',
        'name',
        'email',
        'number',
        'role',
        'password',
        'additional_data',
        'agent_id',
        'is_deleted',
        'additional_data'
    ];

    

    public function landlords()
    {
        return $this->hasMany(User::class, 'agent_id', 'id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id', 'id');
    }

    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function landlordProperties() 
    {
        return $this->properties()->wherePivot('role', 'landlord');
    }

    public function agentProperties()
    {
        return $this->properties()->wherePivot('role', 'agent');
    }

    public function tenancy()
    {
        return $this->hasMany(Tenancy::class);
    }

    public function activeTenancy() 
    {
        return $this->hasOne(Tenancy::class)->where('status', 'active');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     * 
     */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'additional_data'=>'array',
            'is_deleted'=>'boolean',
            'additional_data'=>'array'
        ];
    }
}
