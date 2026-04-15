<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

use App\Models\UserWallet;

class UserWalletSeeder extends Seeder
{
    public function run(): void
    {
        // Load from JSON file
        $jsonPath = database_path('seeders/data/sepolia_wallets.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("JSON file not found: {$jsonPath}");
            return;
        }

        $data = json_decode(File::get($jsonPath), true);

        if (!is_array($data)) {
            $this->command->error("Invalid JSON format.");
            return;
        }

        $count = 0;

        foreach ($data as $i => $wallet) {
            if (!isset($wallet['address'])) continue;

            UserWallet::updateOrCreate(
                ['address' => strtolower($wallet['address'])],
                [
                    'user_id' => $i + 1,
                    // Optional fields like balance/tx_hash can be added
                ]
            );

            $count++;

            if ($count % 1000 === 0) {
                $this->command->info("Inserted {$count} wallets...");
            }
        }

        $this->command->info("✅ Done. Total inserted: {$count}");
    }
}
