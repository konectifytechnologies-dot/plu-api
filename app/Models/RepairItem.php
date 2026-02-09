<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class RepairItem extends Model
{
        use HasUlids;

      protected $keyType = 'string'; 
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $fillable = [
        'id',
        'name',
        'description',
        'cost',
        'repair_id'
    ];

    public function repair()
    {
        return $this->belongsTo(Repair::class);
    }
}
