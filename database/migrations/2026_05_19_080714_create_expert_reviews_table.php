<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expert_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('test_attempts')->cascadeOnDelete();
            $table->foreignId('expert_id')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['pending', 'passed', 'revision_needed'])->default('pending');
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unique('attempt_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_reviews');
    }
};
