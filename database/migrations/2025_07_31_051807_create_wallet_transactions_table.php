<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('user_wallet_id')->nullable()->constrained('user_wallets')->onDelete('set null');
        
            $table->string('from_address')->nullable();
            $table->string('to_address')->nullable();
            $table->string('tx_hash')->unique();
            $table->string('block_number')->nullable();
            $table->string('contract_address')->nullable();
            $table->string('category')->nullable();
            $table->string('asset')->nullable();
            $table->decimal('value', 30, 18)->default(0);
            $table->string('raw_value')->nullable();
            $table->integer('decimals')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
