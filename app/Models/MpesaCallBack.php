<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class MpesaCallBack extends Model
{
    use HasUlids;
    protected $keyType = 'string'; 
    protected $primaryKey = 'id';
    public $incrementing = false; 

    protected $fillable = [
        'id',
        'CheckoutRequestID',
        'user_id',
        'callback_json'
    ];

    protected function casts(): mixed
    {
        return ['callback_json'=>'array'];
    }

    
}
