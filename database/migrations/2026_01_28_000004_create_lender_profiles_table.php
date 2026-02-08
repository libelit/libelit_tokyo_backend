<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lender_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('lender_type')->nullable();
            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->string('kyb_status')->default('pending')->nullable();
            $table->timestamp('kyb_submitted_at')->nullable();
            $table->timestamp('kyb_approved_at')->nullable();
            $table->foreignId('kyb_approved_by')->nullable()->constrained('users');
            $table->text('kyb_rejection_reason')->nullable();
            $table->string('aml_status')->default('pending')->nullable();
            $table->timestamp('aml_checked_at')->nullable();
            $table->string('accreditation_status')->default('pending')->nullable();
            $table->date('accreditation_expires_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('kyb_status');
            $table->index('aml_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lender_profiles');
    }
};
