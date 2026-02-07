<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name')->nullable();
            $table->string('company_registration_number')->nullable();
            $table->text('address')->nullable();
            $table->string('kyb_status')->default('not_started')->nullable();
            $table->timestamp('kyb_submitted_at')->nullable();
            $table->timestamp('kyb_approved_at')->nullable();
            $table->foreignId('kyb_approved_by')->nullable()->constrained('users');
            $table->text('kyb_rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('kyb_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_profiles');
    }
};
