<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('outreach_programs', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique()->index();
            $table->string('category')->index();

            $table->text('description')->nullable();

            $table->decimal('typical_budget', 12, 2)->nullable();
            $table->integer('typical_duration_days')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outreach_programs');
    }
};
