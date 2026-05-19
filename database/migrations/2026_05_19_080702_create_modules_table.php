<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('module_number')->unsigned();
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('requires_expert_review')->default(false);
            $table->tinyInteger('max_test_attempts')->unsigned()->default(3);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
