<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_archives', function (Blueprint $table) {
            $table->id();
            $table->morphs('archivable');
            $table->string('archive_type'); // kyb, project
            $table->string('zip_file_name');
            $table->string('local_path')->nullable();
            $table->string('s3_path')->nullable();
            $table->string('s3_url')->nullable();
            $table->string('s3_bucket')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_hash', 64)->nullable(); // SHA256 = 64 chars
            $table->string('hash_algorithm')->default('sha256');
            $table->unsignedInteger('documents_count')->default(0);
            $table->string('previous_hash', 64)->nullable(); // For hash chain
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index('archive_type');
            $table->index('status');
            $table->index('file_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_archives');
    }
};
