<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $guarded = [];

    
    public function orderItems():HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function userAdresses(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function paytments(): BelongsTo
    {
        return $this->belongsTo(PaymentT::class);
    }
 
}
