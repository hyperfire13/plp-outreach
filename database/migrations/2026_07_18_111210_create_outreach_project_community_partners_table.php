<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'outreach_project_community_partners',
            function (Blueprint $table) {
                $table->id();

                $table->integer('outreach_project_id')
                    ->constrained('outreach_projects')
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->string('organization_name')->nullable();
                $table->string('contact_person')->nullable();

                $table->string('status')
                    ->default('active')
                    ->index();

                $table->timestamps();


            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'outreach_project_community_partners'
        );
    }
};
