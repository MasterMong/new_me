<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_id')->constrained('module_contents')->cascadeOnDelete();
            $table->integer('watch_duration_sec')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->integer('last_position_sec')->default(0);
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_views');
    }
};
