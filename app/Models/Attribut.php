<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribut extends Model
{
    use HasFactory;

    protected $table = 'attributs';
    protected $guarded = [];
    
    public function values(): HasMany
    {
        return $this->hasMany(Value::class);
    }
}
