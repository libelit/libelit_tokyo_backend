<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('developer_id')->constrained('developer_profiles')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('project_type');
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->text('address')->nullable();
            $table->decimal('funding_goal', 18, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('min_investment', 18, 2)->default(0);
            $table->decimal('expected_return', 5, 2)->nullable();
            $table->integer('loan_term_months')->nullable();
            $table->decimal('ltv_ratio', 5, 2)->nullable();
            $table->unsignedTinyInteger('risk_score')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('listed_at')->nullable();
            $table->timestamp('funded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('developer_id');
            $table->index('status');
            $table->index('project_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
