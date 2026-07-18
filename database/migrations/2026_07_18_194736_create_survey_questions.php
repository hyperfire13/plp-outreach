<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('survey_template_id')
                ->constrained('survey_templates')
                ->cascadeOnDelete();

            $table->string('section')->nullable()->index();
            $table->text('question');

            $table->enum('question_type', [
                'text',
                'textarea',
                'number',
                'date',
                'select',
                'radio',
                'checkbox',
                'boolean',
            ])->default('text')->index();

            $table->json('options')->nullable();

            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true)->index();

            $table->unsignedInteger('sort_order')->default(0)->index();

            $table->text('help_text')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['survey_template_id', 'section', 'sort_order'],
                'survey_questions_template_section_sort_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
