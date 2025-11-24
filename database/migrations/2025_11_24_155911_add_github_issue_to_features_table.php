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
        Schema::table('features', function (Blueprint $table) {
            $table->bigInteger('github_issue_number')->nullable()->after('slug');
            $table->string('github_issue_url', 500)->nullable()->after('github_issue_number');
            $table->timestamp('github_synced_at')->nullable()->after('github_issue_url');
            
            $table->index(['project_id', 'github_issue_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'github_issue_number']);
            $table->dropColumn([
                'github_issue_number',
                'github_issue_url',
                'github_synced_at',
            ]);
        });
    }
};
