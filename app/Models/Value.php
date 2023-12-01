<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Value extends Model
{
    use HasFactory;

    protected $table = 'values';
    protected $guarded = [];

    public function attributs(): BelongsTo
    {
        return $this->belongsTo(Attribut::class);
    }
}
