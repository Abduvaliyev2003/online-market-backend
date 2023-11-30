<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramAccounts extends Model
{
    use HasFactory;

    protected $table = 'telegram_accounts';
    protected $guarded = [];


    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
