<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasUlids;

    protected $keyType = 'string'; 
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $fillable = [
        'id',
        'property_id',
        'user_id',
        'tenancy_id',
        'cost_id',
        'payment_method',
        'payment_type',
        'reference_code',
        'date',
        'description',
        'amount_due',
        'amount_paid',
        'balance',
        'year'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function cost()
    {
        return $this->belongsTo(AdditionalCost::class, 'cost_id', 'id');
    }

    protected static function booted()
    {
        static::saving(function ($payment) {
           $payment->balance = max(0,(float) $payment->amount_due - (float) $payment->amount_paid);
        });
    }

}
