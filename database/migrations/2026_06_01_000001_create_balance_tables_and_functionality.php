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
        Schema::create('wallets', static function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable(false);
            $table->decimal('amount', 36, 18)->nullable(false)->default(0);
            $table->string('currency',32)->nullable(false);
            $table->integer('blockchain_id')->nullable(true)->default(0);
            $table->unsignedBigInteger('user_id')->nullable(false);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('restrict')
            ;
        });

        Schema::create('operations', static function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable(false);
            $table->decimal('amount', 36, 18)->nullable(false);
            $table->string('currency',32)->nullable(false);
            $table->integer('blockchain_id')->nullable(true)->default(0);
            $table->unsignedBigInteger('wallet_id')->index('idx_wallet_id')->nullable(false);
            $table->unsignedInteger('op_type')->index('idx_op_type')->nullable(false);
            $table->unsignedInteger('op_state')->index('idx_op_state')->nullable(false);
            $table->jsonb('data')->nullable(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('wallet_id')
                ->references('id')
                ->on('wallets')
                ->onDelete('restrict')
                ->onUpdate('restrict')
            ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
