<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    use HasFactory;

    protected $table = "user_wallets";

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'user_wallet_id');
    }
}
