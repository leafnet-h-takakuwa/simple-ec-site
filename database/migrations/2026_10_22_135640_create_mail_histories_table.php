
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
        Schema::create('mail_histories', function (Blueprint $table) {
            $table->id();

            // ポリモーフィックリレーション
            // related_type / related_id と複合インデックス
            $table->morphs('related');

            // メール種別・送信結果
            $table->string('type', 50);
            $table->string('status', 20);

            // 送信元・送信先
            $table->string('from_address');
            $table->string('to_address');
            $table->text('cc_address')->nullable();
            $table->text('bcc_address')->nullable();

            // 送信したメールの内容
            $table->string('subject');
            $table->longText('body');

            // 送信結果
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();

            // 再送元のメール履歴
            $table->foreignId('resent_from_id')
                ->nullable()
                ->constrained('mail_histories')
                ->nullOnDelete();

            // 再送理由など
            $table->text('send_note')->nullable();

            // 送信した管理者（自動送信の場合はNULL）
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_histories');
    }
};
