<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->enum('content_type', ['video', 'document', 'link']);
            $table->string('title', 500);
            $table->string('file_url', 1000)->nullable();
            $table->decimal('duration_minutes', 8, 2)->nullable();
            $table->integer('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_contents');
    }
};
