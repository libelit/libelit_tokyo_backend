<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_proposals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('lender_id')->constrained('lender_profiles')->onDelete('cascade');
            $table->decimal('loan_amount_offered', 18, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('interest_rate', 5, 2);
            $table->date('loan_maturity_date');
            $table->json('security_packages')->nullable();
            $table->decimal('max_ltv_accepted', 5, 2)->nullable();
            $table->date('bid_expiry_date');
            $table->text('additional_conditions')->nullable();
            $table->string('status')->default('submitted_by_lender');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('developer_signed_at')->nullable();
            $table->timestamp('lender_signed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('project_id');
            $table->index('lender_id');
            $table->index('status');
            $table->index('bid_expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_proposals');
    }
};
