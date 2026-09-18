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
        Schema::table('module_contents', function (Blueprint $table) {
            $table->enum('content_type', ['video', 'document', 'link', 'test', 'worksheet', 'outline'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('module_contents', function (Blueprint $table) {
            $table->enum('content_type', ['video', 'document', 'link', 'test', 'worksheet'])->change();
        });
    }
};
