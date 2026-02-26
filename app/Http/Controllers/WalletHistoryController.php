<?php

namespace App\Http\Controllers;

use App\Models\UserWallet;
use Illuminate\Http\Request;

use App\Models\Wallet;
use App\Models\WalletTransaction;

class WalletHistoryController extends Controller
{
    // Show all wallets
    public function index(Request $request)
    {
        $search = $request->input('search');

        $wallets = UserWallet::when($search, function ($query, $search) {
                $query->where('address', 'LIKE', "%$search%")
                    ->orWhere('user_id', 'LIKE', "%$search%");
            })
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.wallets.index', compact('wallets'));
    }

    // Show wallet transactions
    public function show(UserWallet $wallet)
    {
        $transactions = $wallet->transactions()->latest()->paginate(20);
        return view('admin.wallets.show', compact('wallet', 'transactions'));
    }
}
