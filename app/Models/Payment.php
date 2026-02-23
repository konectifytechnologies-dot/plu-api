<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

    public function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::parse($value)->format('d F Y')
        );
    }

    protected static function booted()
    {
        static::saving(function ($payment) {
           $payment->balance = max(0,(float) $payment->amount_due - (float) $payment->amount_paid);
        });
    }

}
