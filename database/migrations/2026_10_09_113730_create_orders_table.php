<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // ゲスト注文があるので、user_idはnull許可とする
            $table->string('status');
            $table->integer('total_amount');
            $table->string('orderer_name');
            $table->string('orderer_postal_code');
            $table->string('orderer_address');
            $table->string('orderer_phone');
            $table->string('orderer_email')->nullable(); // emailなし、電話連絡考慮でnull許可
            $table->string('shipping_name');
            $table->string('shipping_postal_code');
            $table->string('shipping_address');
            $table->string('shipping_phone');
            $table->timestamps();
            $table->softDeletes();

            // 外部キー制約
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
