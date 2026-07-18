<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('community_id')
                ->constrained('communities')
                ->restrictOnDelete();

            $table->foreignId('survey_template_id')
                ->constrained('survey_templates')
                ->restrictOnDelete();

            $table->date('survey_date')->index();

            $table->string('academic_department')->nullable()->index();
            $table->string('conducted_by')->nullable();

            $table->enum('status', [
                'draft',
                'submitted',
            ])->default('draft')->index();

            $table->text('suggested_outreach_program')->nullable();
            $table->text('remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('submitted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['community_id', 'survey_template_id', 'survey_date'],
                'survey_responses_community_template_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
