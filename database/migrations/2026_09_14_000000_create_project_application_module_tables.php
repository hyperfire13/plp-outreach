<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('priority_needs', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->index();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('validation_remarks')->nullable();
        });

        DB::table('priority_needs')->update([
            'status' => 'validated',
            'validated_at' => now(),
        ]);

        Schema::create('project_proposals', function (Blueprint $table) {
            $table->id();
            $table->string('proposal_number')->nullable()->unique();
            $table->foreignId('priority_need_id')->constrained()->restrictOnDelete();
            $table->foreignId('applicant_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('college_id')->constrained()->restrictOnDelete();
            $table->foreignId('community_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('rationale');
            $table->text('objectives');
            $table->text('beneficiaries');
            $table->text('expected_outputs');
            $table->text('expected_outcomes');
            $table->text('sustainability_plan');
            $table->text('risk_assessment');
            $table->text('monitoring_indicators');
            $table->text('sdg_alignment');
            $table->text('development_plan_alignment');
            $table->text('partner_involvement')->nullable();
            $table->decimal('proposed_budget', 14, 2)->default(0);
            $table->string('status', 32)->default('draft')->index();
            $table->string('current_step', 48)->nullable()->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['college_id', 'status']);
        });

        Schema::create('project_proposal_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_proposal_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('estimated_cost', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('project_proposal_workplans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_proposal_id')->constrained()->cascadeOnDelete();
            $table->string('activity');
            $table->text('expected_output')->nullable();
            $table->string('responsible_person')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('estimated_cost', 14, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_proposal_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_proposal_id')->constrained()->cascadeOnDelete();
            $table->string('step', 48);
            $table->string('decision', 24);
            $table->foreignId('acted_by')->constrained('users')->restrictOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('acted_at');
            $table->timestamps();
            $table->index(['project_proposal_id', 'step']);
        });

        Schema::create('project_proposal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_proposal_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->string('original_name');
            $table->string('storage_path');
            $table->string('disk', 30)->default('local');
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('notice_to_proceeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_proposal_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('ntp_number')->unique();
            $table->foreignId('issued_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('issued_at');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80)->index();
            $table->string('module', 80)->index();
            $table->nullableMorphs('auditable');
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('request_id', 64)->nullable()->index();
            $table->string('route_name')->nullable();
            $table->string('http_method', 10)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notice_to_proceeds');
        Schema::dropIfExists('project_proposal_documents');
        Schema::dropIfExists('project_proposal_approvals');
        Schema::dropIfExists('project_proposal_workplans');
        Schema::dropIfExists('project_proposal_resources');
        Schema::dropIfExists('project_proposals');
        Schema::table('priority_needs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('validated_by');
            $table->dropColumn(['status', 'validated_at', 'validation_remarks']);
        });
    }
};
