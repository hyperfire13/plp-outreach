<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'outreach_project_evaluators',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('outreach_project_id')
                    ->constrained('outreach_projects')
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->string('evaluation_type')->nullable();

                $table->string('status')
                    ->default('assigned')
                    ->index();

                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('completed_at')->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'outreach_project_evaluators'
        );
    }
};
