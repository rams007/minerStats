<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMiningStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mining_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id')->index();
            $table->timestamp('time');
            $table->double('reported_hashrate');
            $table->double('current_hashrate');
            $table->unsignedInteger('valid_shares');
            $table->unsignedInteger('invalid_shares');
            $table->unsignedInteger('stale_shares');
            $table->double('average_hashrate');
            $table->unsignedInteger('active_workers');
            $table->timestamps();
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
            $table->unique(['wallet_id','time']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mining_stats');
    }
}
