<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasUlids;

    protected $keyType = 'string'; 
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $fillable = [
        'id',
        'invoice_id',
        'item_name',
        'description',
        'amount',
        'quantity',
        'total'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
