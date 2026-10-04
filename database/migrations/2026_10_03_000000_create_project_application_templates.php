<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_proposals', function (Blueprint $table) {
            $table->foreignId('college_id')->nullable()->change();
        });

        Schema::create('project_application_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80);
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('phase', 32)->index();
            $table->string('status', 20)->default('draft')->index();
            $table->json('schema');
            $table->boolean('is_required')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['code', 'version']);
            $table->index(['status', 'sort_order']);
        });

        Schema::create('project_proposal_template_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_proposal_id')
                ->constrained('project_proposals', 'id', 'pptr_proposal_fk')
                ->cascadeOnDelete();
            $table->foreignId('project_application_template_id')
                ->constrained(
                    'project_application_templates',
                    'id',
                    'pptr_template_fk'
                )
                ->restrictOnDelete();
            $table->json('response_data');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['project_proposal_id', 'project_application_template_id'],
                'proposal_template_response_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_proposal_template_responses');
        Schema::dropIfExists('project_application_templates');

        Schema::table('project_proposals', function (Blueprint $table) {
            $table->foreignId('college_id')->nullable(false)->change();
        });
    }
};
