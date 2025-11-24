<?php

declare(strict_types=1);

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
            $table->string('github_owner', 191)->nullable()->after('description');
            $table->string('github_repo', 191)->nullable()->after('github_owner');
            $table->boolean('github_sync_enabled')->default(false)->after('github_repo');
            $table->timestamp('github_last_sync')->nullable()->after('github_sync_enabled');
            $table->json('github_sync_config')->nullable()->after('github_last_sync');
            
            $table->index(['github_owner', 'github_repo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['github_owner', 'github_repo']);
            $table->dropColumn([
                'github_owner',
                'github_repo',
                'github_sync_enabled',
                'github_last_sync',
                'github_sync_config',
            ]);
        });
    }
};
