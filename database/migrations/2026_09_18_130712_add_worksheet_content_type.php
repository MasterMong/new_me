<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_contents', function (Blueprint $table) {
            $table->enum('content_type', ['video', 'document', 'link', 'test', 'worksheet'])->change();
            $table->string('answer_key_url', 1000)->nullable()->after('file_url');
        });
    }

    public function down(): void
    {
        Schema::table('module_contents', function (Blueprint $table) {
            $table->dropColumn('answer_key_url');
            $table->enum('content_type', ['video', 'document', 'link', 'test'])->change();
        });
    }
};
