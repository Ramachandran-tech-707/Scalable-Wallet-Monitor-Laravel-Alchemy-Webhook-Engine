<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\Log;
use Throwable;

use App\Models\UserWallet;
use App\Models\WalletTransaction;

class ProcessUsdtDeposit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $activity;

    /**
     * Create a new job instance.
     */
    public function __construct(array $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $fromAddress = strtolower($this->activity['fromAddress'] ?? '');
            $toAddress   = strtolower($this->activity['toAddress'] ?? '');
            $txHash      = $this->activity['hash'] ?? null;
            $category    = $this->activity['category'] ?? null;
            $asset       = $this->activity['asset'] ?? null;
            $blockNum    = $this->activity['blockNum'] ?? null;
            $value       = $this->activity['value'] ?? '0';
            $rawContract = $this->activity['rawContract'] ?? [];

            // Basic validation
            if (!$txHash || !$toAddress) {
                Log::warning("Missing txHash or toAddress in activity. Skipping.");
                return;
            }

            // Skip duplicate transaction
            $exists = WalletTransaction::where('tx_hash', $txHash)->exists();
            if ($exists) {
                Log::info("Duplicate TX skipped: {$txHash}");
                return;
            }

            // Find wallet
            $wallet = UserWallet::whereRaw('LOWER(address) = ?', [$toAddress])->first();
            if (!$wallet) {
                Log::warning("Wallet not found for toAddress: {$toAddress}. Skipping.");
                return;
            }

            // Calculate amount
            $amount = number_format((float)$value, 6, '.', '');

            // Update wallet (manual assignment)
            $wallet->balance = bcadd($wallet->balance, $amount, 6);
            $wallet->last_tx_hash = $txHash;
            $wallet->save();

            // Create transaction log
            $tx = new WalletTransaction();
            $tx->user_wallet_id = $wallet->id;
            $tx->from_address = $fromAddress;
            $tx->to_address = $toAddress;
            $tx->tx_hash = $txHash;
            $tx->block_number = $blockNum;
            $tx->contract_address = $rawContract['address'] ?? null;
            $tx->category = $category;
            $tx->asset = $asset;
            $tx->value = $value;
            $tx->raw_value = $rawContract['rawValue'] ?? null;
            $tx->decimals = $rawContract['decimals'] ?? 18;
            $tx->save();

            Log::info("Deposit processed: {$amount} {$asset} to user_id={$wallet->user_id}, tx={$txHash}");
        }
        catch (Throwable $e) {
            Log::error("Error processing activity: " . $e->getMessage(), ['activity' => $this->activity]);
        }
    }
}
