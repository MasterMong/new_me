<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->enum('prefix', ['นาย', 'นาง', 'นางสาว'])->after('id');
            $table->string('first_name', 100)->after('prefix');
            $table->string('last_name', 100)->after('first_name');
            $table->foreignId('position_id')->nullable()->after('last_name')
                ->constrained('positions')->nullOnDelete();
            $table->string('position_other')->nullable()->after('position_id');
            $table->foreignId('affiliation_id')->nullable()->after('position_other')
                ->constrained('affiliations')->nullOnDelete();
            $table->string('school_name')->nullable()->after('affiliation_id');
            $table->enum('experience', ['<2y', '2-5y', '5-10y', '10-20y', '>20y'])
                ->nullable()->after('school_name');
            $table->string('phone', 20)->nullable()->after('experience');
            $table->enum('role', ['learner', 'expert', 'admin'])->default('learner')->after('phone');
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropForeign(['affiliation_id']);
            $table->dropColumn([
                'prefix', 'first_name', 'last_name', 'position_id', 'position_other',
                'affiliation_id', 'school_name', 'experience', 'phone', 'role', 'is_active',
            ]);
            $table->string('name')->after('id');
        });
    }
};
