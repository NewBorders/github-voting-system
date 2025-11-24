<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Add github_token column (optional, for private repos)
            $table->string('github_token', 500)->nullable()->after('github_repo');
            
            // Remove github_sync_enabled column (always enabled now)
            $table->dropColumn('github_sync_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Remove github_token
            $table->dropColumn('github_token');
            
            // Restore github_sync_enabled
            $table->boolean('github_sync_enabled')->default(false)->after('github_repo');
        });
    }
};
