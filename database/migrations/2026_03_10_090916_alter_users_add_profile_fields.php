<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('first_name')->after('id')->nullable();
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->after('middle_name')->nullable();
             $table->string('name')->nullable();
            $table->date('birthday')->nullable()->after('last_name');

            $table->string('contact_number')->nullable()->after('birthday');

            // remove old name if you want normalized structure
            // $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('name');

            $table->dropColumn([
                'first_name',
                'middle_name',
                'last_name',
                'birthday',
                'contact_number'
            ]);
        });
    }
};
