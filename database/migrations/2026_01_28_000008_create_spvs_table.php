<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spvs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('registration_number')->nullable();
            $table->string('jurisdiction')->nullable();
            $table->string('collateral_type')->nullable();
            $table->text('collateral_description')->nullable();
            $table->decimal('collateral_value', 18, 2)->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('created_at_blockchain')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('project_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spvs');
    }
};
