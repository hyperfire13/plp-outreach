<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outreach_records', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('execution_date')
                ->constrained('users')
                ->nullOnDelete();
            $table->index(['college_id', 'execution_date']);
        });
    }

    public function down(): void
    {
        Schema::table('outreach_records', function (Blueprint $table) {
            $table->dropIndex(['college_id', 'execution_date']);
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
