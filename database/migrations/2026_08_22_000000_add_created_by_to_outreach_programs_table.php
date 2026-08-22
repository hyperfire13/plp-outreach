<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outreach_programs', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('is_active')
                ->constrained('users')
                ->nullOnDelete();
        });

        $fallbackCreatorId = DB::table('users')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->whereIn('roles.name', ['calo_administrator', 'super_admin'])
            ->orderByRaw("CASE WHEN roles.name = 'calo_administrator' THEN 0 ELSE 1 END")
            ->value('users.id');

        if ($fallbackCreatorId !== null) {
            DB::table('outreach_programs')
                ->whereNull('created_by')
                ->update(['created_by' => $fallbackCreatorId]);
        }
    }

    public function down(): void
    {
        Schema::table('outreach_programs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
