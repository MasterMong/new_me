<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_group_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('module_contents')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('learner_groups')->cascadeOnDelete();
            $table->unique(['content_id', 'group_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_group_access');
    }
};
