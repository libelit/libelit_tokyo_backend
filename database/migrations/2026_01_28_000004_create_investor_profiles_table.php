<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('investor_type')->nullable();
            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->string('kyc_status')->default('pending')->nullable();
            $table->timestamp('kyc_submitted_at')->nullable();
            $table->timestamp('kyc_approved_at')->nullable();
            $table->foreignId('kyc_approved_by')->nullable()->constrained('users');
            $table->text('kyc_rejection_reason')->nullable();
            $table->string('aml_status')->default('pending')->nullable();
            $table->timestamp('aml_checked_at')->nullable();
            $table->string('accreditation_status')->default('pending')->nullable();
            $table->date('accreditation_expires_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('kyc_status');
            $table->index('aml_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_profiles');
    }
};
