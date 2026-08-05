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
        Schema::create('module_expert_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expert_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->unique(['module_id', 'expert_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_expert_assignments');
    }
};
