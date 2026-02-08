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
            $table->decimal('loan_amount', 18, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('min_investment', 18, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('listed_at')->nullable();
            $table->timestamp('funded_at')->nullable();
            $table->date('construction_start_date')->nullable();
            $table->date('construction_end_date')->nullable();
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
