<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communities', function (Blueprint $table) {
            $table->id();

            $table->string('name')->index();
            $table->string('region')->nullable()->index();

            $table->unsignedInteger('population')->nullable();

            $table->decimal('poverty_rate', 5, 2)->nullable();          // %
            $table->decimal('unemployment_rate', 5, 2)->nullable();     // %
            $table->decimal('literacy_rate', 5, 2)->nullable();         // %

            $table->decimal('avg_income', 12, 2)->nullable();

            $table->enum('urban_rural', ['urban', 'rural'])->nullable();

            $table->enum('disaster_risk_level', ['low','medium','high'])->nullable();

            $table->decimal('health_risk_index', 5, 2)->nullable();
            $table->decimal('infrastructure_score', 5, 2)->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};