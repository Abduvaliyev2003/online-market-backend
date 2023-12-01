<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'stocks';
    protected $guarded = [];
      
    protected $casts =
    [
       'attributes' => 'array' 
    ];

    public function products():BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
