
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fax_order_form_rows', function (Blueprint $table) {
            $table->id();

            // 掲載する列と並び順
            $table->string('column', 10);
            $table->unsignedInteger('sort_order');

            // 商品またはメッセージ
            $table->string('type', 10);

            // 商品行の場合のみ使用
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->restrictOnDelete();

            $table->string('display_name', 100)->nullable();

            // メッセージ行の場合のみ使用
            $table->string('message', 255)->nullable();

            $table->timestamps();

            // 列と並び順による取得を高速化
            $table->index(['column', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fax_order_form_rows');
    }
};
