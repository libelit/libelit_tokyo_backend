<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('spv_id')->constrained()->onDelete('cascade');
            $table->string('xrpl_mpt_id')->unique()->nullable();
            $table->string('xrpl_issuer_address')->nullable();
            $table->string('name');
            $table->string('symbol');
            $table->decimal('total_supply', 18, 8);
            $table->decimal('issued_supply', 18, 8)->default(0);
            $table->decimal('available_supply', 18, 8)->default(0);
            $table->decimal('price_per_token', 18, 8);
            $table->integer('decimals')->default(8);
            $table->string('metadata_uri')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('minted_at')->nullable();
            $table->timestamps();

            $table->index('project_id');
            $table->index('spv_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
