<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_templates', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            $table->unsignedInteger('version')->default(1);

            $table->enum('status', [
                'draft',
                'published',
                'archived',
            ])->default('draft')->index();

            $table->boolean('is_default')->default(false)->index();

            $table->timestamp('published_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['title', 'version', 'deleted_at'],
                'survey_templates_title_version_deleted_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_templates');
    }
};
