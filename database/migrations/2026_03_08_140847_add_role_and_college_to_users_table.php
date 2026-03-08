<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->foreignId('role_id')
                ->after('id')
                ->constrained('roles')
                ->cascadeOnDelete();

            $table->foreignId('college_id')
                ->nullable()
                ->after('role_id')
                ->constrained('colleges')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign(['role_id']);
            $table->dropForeign(['college_id']);

            $table->dropColumn(['role_id','college_id']);

        });
    }
};