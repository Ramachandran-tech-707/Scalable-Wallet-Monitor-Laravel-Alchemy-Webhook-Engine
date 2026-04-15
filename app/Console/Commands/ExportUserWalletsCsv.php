<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ExportUserWalletsCsv extends Command
{
    protected $signature = 'export:user-wallets';
    protected $description = 'Export all wallet addresses from user_wallets table to a CSV file';

    public function handle()
    {
        $filePath = 'exports/user_wallets.csv';
        $wallets = DB::table('user_wallets')->pluck('address');

        // Ensure export directory exists
        Storage::makeDirectory('exports');

        // Write to CSV without header
        Storage::put($filePath, $wallets->implode(PHP_EOL));

        $this->info("Wallet addresses exported to: storage/app/{$filePath}");
    }
}