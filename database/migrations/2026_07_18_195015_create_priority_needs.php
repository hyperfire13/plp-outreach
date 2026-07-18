<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('priority_needs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('survey_response_id')
                ->constrained('survey_responses')
                ->cascadeOnDelete();

            $table->foreignId('community_id')
                ->constrained('communities')
                ->restrictOnDelete();

            $table->string('need')->index();

            $table->unsignedTinyInteger('priority_rank');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(
                ['survey_response_id', 'priority_rank'],
                'priority_needs_response_rank_unique'
            );

            $table->unique(
                ['survey_response_id', 'need'],
                'priority_needs_response_need_unique'
            );

            $table->index(
                ['community_id', 'need'],
                'priority_needs_community_need_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('priority_needs');
    }
};
