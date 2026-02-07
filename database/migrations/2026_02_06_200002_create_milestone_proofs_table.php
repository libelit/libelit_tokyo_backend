<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milestone_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milestone_id')->constrained('project_milestones')->onDelete('cascade');
            $table->string('proof_type'); // photo, invoice, inspection_report, bank_statement, other
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->string('storage_disk')->default('local');
            $table->string('s3_path')->nullable();
            $table->string('s3_url')->nullable();
            $table->string('s3_bucket')->nullable();
            $table->string('s3_status')->default('pending');
            $table->timestamp('uploaded_to_s3_at')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('milestone_id');
            $table->index('proof_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestone_proofs');
    }
};
