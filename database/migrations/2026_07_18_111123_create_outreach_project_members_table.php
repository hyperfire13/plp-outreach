<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outreach_project_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('outreach_project_id')
                ->constrained('outreach_projects')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('member_role')->nullable();

            $table->string('status')
                ->default('active')
                ->index();

            $table->timestamp('joined_at')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_project_members');
    }
};
