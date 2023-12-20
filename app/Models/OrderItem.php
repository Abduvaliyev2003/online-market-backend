<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

 
    protected $guarded = [];

 

    public function orders():BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function products():BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
