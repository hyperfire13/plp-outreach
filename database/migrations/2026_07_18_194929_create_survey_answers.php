<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('survey_response_id')
                ->constrained('survey_responses')
                ->cascadeOnDelete();

            $table->foreignId('survey_question_id')
                ->constrained('survey_questions')
                ->restrictOnDelete();

            $table->text('answer_text')->nullable();
            $table->decimal('answer_number', 15, 2)->nullable();
            $table->date('answer_date')->nullable();
            $table->boolean('answer_boolean')->nullable();
            $table->json('answer_json')->nullable();

            $table->timestamps();

            $table->unique(
                ['survey_response_id', 'survey_question_id'],
                'survey_answers_response_question_unique'
            );

            $table->index(
                ['survey_question_id', 'survey_response_id'],
                'survey_answers_question_response_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};
