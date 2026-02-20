<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasUlids;

    protected $keyType = 'string'; 
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $fillable = [
        'id',
        'property_id',
        'unit_id',
        'invoice_number',
        'status',
        'invoice_url',
        'due_date'
    ];

    public function reminders()
    {
        return $this->hasMany(PaymentReminder::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    protected function casts(): mixed
    {
        return ['invoice_reminder_data'=>'array'];
    }
}
