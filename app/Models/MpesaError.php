<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class MpesaError extends Model
{
    use HasUlids;

    protected $keyType = 'string'; 
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $fillable = [
        'id',
        'MerchantRequestID',
        'CheckoutRequestID',
        'ResultCode',
        'ResultDescription',
    ];
}
