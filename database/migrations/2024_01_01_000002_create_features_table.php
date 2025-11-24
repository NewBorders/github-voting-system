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
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('title', 191);
            $table->string('slug', 191);
            $table->text('description')->nullable();
            $table->enum('status', [
                'submitted',
                'accepted',
                'planned',
                'in_progress',
                'done',
                'rejected',
            ])->default('submitted');
            $table->integer('vote_count')->default(0)->unsigned();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'slug']);
            $table->index('project_id');
            $table->index('status');
            $table->index('vote_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
