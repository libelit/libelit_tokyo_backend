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
        Schema::create('blockchain_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->nullableMorphs('auditable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('event_data');
            $table->string('data_hash', 64);
            $table->string('tx_hash')->nullable()->unique();
            $table->foreignId('xrpl_transaction_id')->nullable()->constrained('xrpl_transactions')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->index('event_type');
            $table->index('status');
            $table->index('data_hash');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchain_audit_logs');
    }
};
