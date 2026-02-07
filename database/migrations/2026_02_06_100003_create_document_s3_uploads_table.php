<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_s3_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('archive_id')->nullable()->constrained('document_archives')->nullOnDelete();
            $table->string('upload_type'); // document, archive
            $table->string('source_path');
            $table->string('destination_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('max_attempts')->default(3);
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->text('error_trace')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('upload_type');
            $table->index('error_code');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_s3_uploads');
    }
};
