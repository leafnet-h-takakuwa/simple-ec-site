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
        Schema::table('users', function (Blueprint $table) {
            // 変更申請中のメールアドレス
            $table->string('pending_email')->nullable();

            // メール変更認証用トークンのハッシュ
            $table->string('pending_email_token', 64)->nullable();

            // メール変更申請日時
            $table->timestamp('pending_email_requested_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'pending_email',
                'pending_email_token',
                'pending_email_requested_at',
            ]);
        });
    }
};
