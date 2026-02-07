<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('storage_disk')->default('local')->after('file_path');
            $table->string('s3_path')->nullable()->after('storage_disk');
            $table->string('s3_url')->nullable()->after('s3_path');
            $table->string('s3_bucket')->nullable()->after('s3_url');
            $table->string('s3_status')->default('pending')->after('s3_bucket');
            $table->timestamp('uploaded_to_s3_at')->nullable()->after('s3_status');

            $table->index('storage_disk');
            $table->index('s3_status');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['storage_disk']);
            $table->dropIndex(['s3_status']);
            $table->dropColumn([
                'storage_disk',
                's3_path',
                's3_url',
                's3_bucket',
                's3_status',
                'uploaded_to_s3_at',
            ]);
        });
    }
};
