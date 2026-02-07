<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing records that have 'pending' status but no kyb_submitted_at
        // These are new developers who haven't actually submitted KYB yet
        DB::table('developer_profiles')
            ->where('kyb_status', 'pending')
            ->whereNull('kyb_submitted_at')
            ->update(['kyb_status' => 'not_started']);

        // Change the default value for the column
        Schema::table('developer_profiles', function (Blueprint $table) {
            $table->string('kyb_status')->default('not_started')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('developer_profiles', function (Blueprint $table) {
            $table->string('kyb_status')->default('pending')->nullable()->change();
        });

        // Revert records back to pending
        DB::table('developer_profiles')
            ->where('kyb_status', 'not_started')
            ->update(['kyb_status' => 'pending']);
    }
};
