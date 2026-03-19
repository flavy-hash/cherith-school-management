<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('tra_receipt_number')->nullable()->after('transaction_id');
            $table->string('tra_verification_code')->nullable()->after('tra_receipt_number');
            $table->text('tra_qr_code')->nullable()->after('tra_verification_code');
            $table->json('tra_receipt_payload')->nullable()->after('tra_qr_code');
            $table->timestamp('tra_receipt_synced_at')->nullable()->after('tra_receipt_payload');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'tra_receipt_number',
                'tra_verification_code',
                'tra_qr_code',
                'tra_receipt_payload',
                'tra_receipt_synced_at',
            ]);
        });
    }
};
