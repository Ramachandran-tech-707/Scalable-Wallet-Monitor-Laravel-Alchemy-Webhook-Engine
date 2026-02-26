<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $table = "wallet_transactions";

    public function wallet()
    {
        return $this->belongsTo(UserWallet::class, 'user_wallet_id');
    }
}
