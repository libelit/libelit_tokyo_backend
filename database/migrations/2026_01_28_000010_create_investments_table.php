<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained();
            $table->foreignId('lender_id')->constrained('lender_profiles');
            $table->foreignId('token_id')->nullable()->constrained();
            $table->decimal('amount', 18, 2);
            $table->decimal('token_quantity', 18, 8);
            $table->string('payment_method');
            $table->string('payment_currency')->default('USD');
            $table->string('payment_reference')->nullable();
            $table->string('xrpl_tx_hash')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index('project_id');
            $table->index('lender_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
