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
        Schema::table('milestone_proofs', function (Blueprint $table) {
            $table->boolean('is_payment_proof')->default(false)->after('uploaded_by');
            $table->foreignId('payment_uploaded_by')
                ->nullable()
                ->after('is_payment_proof')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('milestone_proofs', function (Blueprint $table) {
            $table->dropForeign(['payment_uploaded_by']);
            $table->dropColumn(['is_payment_proof', 'payment_uploaded_by']);
        });
    }
};
