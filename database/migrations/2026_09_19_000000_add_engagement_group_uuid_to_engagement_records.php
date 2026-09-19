<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('engagement_records', function (Blueprint $table) {
            $table->uuid('engagement_group_uuid')->nullable()->after('id')->index();
        });

        DB::table('engagement_records')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($records): void {
                foreach ($records as $record) {
                    DB::table('engagement_records')
                        ->where('id', $record->id)
                        ->update(['engagement_group_uuid' => (string) Str::uuid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('engagement_records', function (Blueprint $table) {
            $table->dropColumn('engagement_group_uuid');
        });
    }
};
