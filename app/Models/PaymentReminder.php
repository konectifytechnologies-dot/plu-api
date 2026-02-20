<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class PaymentReminder extends Model
{
    use HasUlids;

    protected $keyType = 'string'; 
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $fillable = [
        'id',
        'invoice_id',
        'type',
        'sent_at',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
