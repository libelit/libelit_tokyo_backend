<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sequence')->default(1);
            $table->decimal('amount', 18, 2);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->string('status')->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('proof_submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('project_id');
            $table->index('status');
            $table->index('sequence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};
