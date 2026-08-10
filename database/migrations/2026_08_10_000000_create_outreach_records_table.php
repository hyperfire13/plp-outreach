<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outreach_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')
                ->constrained('communities')
                ->restrictOnDelete();
            $table->foreignId('outreach_program_id')
                ->constrained('outreach_programs')
                ->restrictOnDelete();
            $table->foreignId('college_id')
                ->constrained('colleges')
                ->restrictOnDelete();
            $table->decimal('budget_used', 12, 2)->nullable();
            $table->unsignedInteger('volunteers_count')->default(0);
            $table->decimal('impact_score', 5, 2)->nullable();
            $table->decimal('success_rate', 5, 2)->nullable();
            $table->decimal('satisfaction_rating', 3, 2)->nullable();
            $table->date('execution_date')->index();
            $table->timestamps();

            $table->unique(
                ['community_id', 'outreach_program_id', 'college_id', 'execution_date'],
                'outreach_records_relationship_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_records');
    }
};
