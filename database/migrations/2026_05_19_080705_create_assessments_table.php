<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['pre_test', 'post_test', 'module_test', 'assignment']);
            $table->string('title', 500);
            $table->integer('passing_score_pct')->default(60);
            $table->tinyInteger('max_attempts')->unsigned()->default(3);
            $table->enum('grading_mode', ['auto', 'manual', 'mixed']);
            $table->boolean('is_required_for_cert')->default(false);
            $table->boolean('requires_expert_review')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
