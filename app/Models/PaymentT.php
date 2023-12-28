<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentT extends Model
{
    use HasFactory;
     
    protected $table = 'payment_t_s';
    protected $guarded = [];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
