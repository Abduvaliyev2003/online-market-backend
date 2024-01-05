<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentModel extends Model
{
    use HasFactory;

    protected $table = 'comment_models';
    protected $guarded = [];


    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
