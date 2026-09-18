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
        Schema::table('courses', function (Blueprint $table) {
            $table->text('target_audience')->nullable()->after('description');
            $table->text('learning_format')->nullable()->after('target_audience');
            $table->text('completion_criteria')->nullable()->after('learning_format');
            $table->text('instructor_team')->nullable()->after('completion_criteria');
            $table->text('certification_info')->nullable()->after('instructor_team');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'target_audience', 'learning_format', 'completion_criteria', 'instructor_team', 'certification_info',
            ]);
        });
    }
};
