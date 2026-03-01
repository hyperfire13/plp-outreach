<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique()->index();
            $table->string('code', 50)->unique()->nullable()->index();

            $table->string('type')->nullable(); 
            // e.g. public, private, state university, etc.

            $table->string('location')->nullable()->index();

            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colleges');
    }
};