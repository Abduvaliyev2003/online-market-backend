<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

 
    protected $guarded = [];

    // protected $casts =
    // [
    //    'product' => 'array' 
    // ];

    public function orders():BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
