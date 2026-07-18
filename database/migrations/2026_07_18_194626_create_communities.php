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
            $table->string('barangay_code')->nullable()->unique();
            $table->string('city')->default('Pasig City');
            $table->string('province')->nullable();

            $table->unsignedBigInteger('estimated_population')->nullable();
            $table->unsignedInteger('estimated_households')->nullable();

            $table->string('community_type')->nullable()->index();
            $table->string('predominant_livelihood')->nullable();

            $table->text('address')->nullable();
            $table->text('remarks')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['name', 'city', 'deleted_at'],
                'communities_name_city_deleted_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
