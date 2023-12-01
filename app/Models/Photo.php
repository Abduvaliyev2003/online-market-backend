<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Photo extends Model
{
    use HasFactory;

    protected $table = 'photos';
    protected $guarded = [];
    
    /**
     * Get the parent photoable model .
     */

    public function photoable(): MorphTo
    {
        return $this->morphTo();
    }
}
