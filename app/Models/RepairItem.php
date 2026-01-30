<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairItem extends Model
{
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
