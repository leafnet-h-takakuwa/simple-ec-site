
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
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);

            // email / phone / fax / in_person / none
            $table->string('contact_method', 20)->default('none');

            // 連絡内容・メールを送らなかった理由など
            $table->text('contact_note')->nullable();

            // 操作した管理者（自動処理の場合はNULL）
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
