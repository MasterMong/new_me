<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->enum('prerequisite_type', ['module', 'assessment']);
            $table->foreignId('prerequisite_module_id')->nullable()
                ->constrained('modules')->nullOnDelete();
            $table->foreignId('prerequisite_assessment_id')->nullable()
                ->constrained('assessments')->nullOnDelete();
            $table->integer('min_score_pct')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_prerequisites');
    }
};
