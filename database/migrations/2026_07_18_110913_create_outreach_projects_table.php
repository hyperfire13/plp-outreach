<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outreach_projects', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Catalog and organizational relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('outreach_program_id')
                ->constrained('outreach_programs')
                ->restrictOnDelete();

            $table->foreignId('college_id')
                ->constrained('colleges')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Ownership and assignment
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('coordinator_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Project details
            |--------------------------------------------------------------------------
            */

            $table->string('title');
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();

            $table->string('location')->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->decimal('proposed_budget', 12, 2)->nullable();

            $table->unsignedInteger('expected_beneficiaries')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

            $table->string('status')
                ->default('draft')
                ->index();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['college_id', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['coordinator_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_projects');
    }
};
