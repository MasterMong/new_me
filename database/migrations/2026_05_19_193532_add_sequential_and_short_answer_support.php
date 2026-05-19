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
        Schema::table('modules', function (Blueprint $table) {
            $table->boolean('is_sequential')->default(false)->after('thumbnail_url');
        });

        Schema::table('module_contents', function (Blueprint $table) {
            $table->enum('content_type', ['video', 'document', 'link', 'test'])->change();
            $table->foreignId('assessment_id')->nullable()->after('content_type')->constrained('assessments')->nullOnDelete();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->enum('question_type', ['multiple_choice', 'essay', 'file_upload', 'short_answer'])->change();
            $table->string('correct_answer')->nullable()->after('question_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('correct_answer');
            $table->enum('question_type', ['multiple_choice', 'essay', 'file_upload'])->change();
        });

        Schema::table('module_contents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assessment_id');
            $table->enum('content_type', ['video', 'document', 'link'])->change();
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('is_sequential');
        });
    }
};
