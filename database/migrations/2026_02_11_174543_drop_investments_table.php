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
        Schema::dropIfExists('investments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('lender_id')->constrained('lender_profiles')->onDelete('cascade');
            $table->foreignId('token_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->decimal('token_quantity', 18, 8)->nullable();
            $table->string('payment_method');
            $table->string('payment_currency', 10)->default('USD');
            $table->string('payment_reference')->nullable();
            $table->string('xrpl_tx_hash')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['lender_id', 'status']);
        });
    }
};
