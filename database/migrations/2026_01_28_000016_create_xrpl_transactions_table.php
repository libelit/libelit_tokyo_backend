<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xrpl_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tx_hash')->unique();
            $table->string('tx_type');
            $table->string('from_address');
            $table->string('to_address')->nullable();
            $table->decimal('amount', 18, 8)->nullable();
            $table->string('currency')->nullable();
            $table->decimal('fee', 18, 8)->nullable();
            $table->unsignedBigInteger('sequence')->nullable();
            $table->unsignedBigInteger('ledger_index')->nullable();
            $table->string('status')->default('pending');
            $table->nullableMorphs('related');
            $table->json('raw_response')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->index('tx_hash');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xrpl_transactions');
    }
};
