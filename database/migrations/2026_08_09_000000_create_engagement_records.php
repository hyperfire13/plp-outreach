<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('outreach_project_id')
                ->nullable()
                ->constrained('outreach_projects')
                ->restrictOnDelete();
            $table->foreignId('community_id')
                ->nullable()
                ->constrained('communities')
                ->restrictOnDelete();

            $table->string('title', 255);
            $table->string('engagement_type', 50)->index();
            $table->string('participation_role', 150);
            $table->date('activity_date')->index();
            $table->decimal('service_hours', 8, 2)->nullable();
            $table->string('sdg', 50)->nullable()->index();
            $table->text('description')->nullable();

            $table->string('source_type', 30)->default('manual')->index();
            $table->string('status', 30)->default('draft')->index();

            $table->foreignId('encoded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->text('validation_remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['user_id', 'status', 'activity_date'],
                'engagement_records_user_status_date_index'
            );
            $table->index(
                ['outreach_project_id', 'source_type'],
                'engagement_records_project_source_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_records');
    }
};
