<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $dbName = (string) DB::getDatabaseName();

        $foreignKeys = collect(DB::select(
            'SELECT CONSTRAINT_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$dbName, 'teacher_subjects'],
        ));

        $userFk = $foreignKeys->firstWhere('COLUMN_NAME', 'user_id')?->CONSTRAINT_NAME;
        $subjectFk = $foreignKeys->firstWhere('COLUMN_NAME', 'subject_id')?->CONSTRAINT_NAME;

        Schema::table('teacher_subjects', function (Blueprint $table) use ($userFk, $subjectFk) {
            if ($userFk) {
                DB::statement('ALTER TABLE teacher_subjects DROP FOREIGN KEY ' . $userFk);
            }

            if ($subjectFk) {
                DB::statement('ALTER TABLE teacher_subjects DROP FOREIGN KEY ' . $subjectFk);
            }

            $indexes = collect(DB::select('SHOW INDEX FROM teacher_subjects'))
                ->pluck('Key_name')
                ->unique();

            if ($indexes->contains('teacher_subjects_user_id_unique')) {
                $table->dropUnique('teacher_subjects_user_id_unique');
            }

            if ($indexes->contains('teacher_subjects_user_id_subject_id_unique')) {
                $table->dropUnique('teacher_subjects_user_id_subject_id_unique');
            }

            if (! $indexes->contains('teacher_subjects_user_subject_standard_unique')) {
                $table->unique(['user_id', 'subject_id', 'standard_id'], 'teacher_subjects_user_subject_standard_unique');
            }
        });

        Schema::table('teacher_subjects', function (Blueprint $table) use ($userFk, $subjectFk) {
            if ($userFk) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            }

            if ($subjectFk) {
                $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dbName = (string) DB::getDatabaseName();

        $foreignKeys = collect(DB::select(
            'SELECT CONSTRAINT_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$dbName, 'teacher_subjects'],
        ));

        $userFk = $foreignKeys->firstWhere('COLUMN_NAME', 'user_id')?->CONSTRAINT_NAME;
        $subjectFk = $foreignKeys->firstWhere('COLUMN_NAME', 'subject_id')?->CONSTRAINT_NAME;

        Schema::table('teacher_subjects', function (Blueprint $table) use ($userFk, $subjectFk) {
            if ($userFk) {
                DB::statement('ALTER TABLE teacher_subjects DROP FOREIGN KEY ' . $userFk);
            }

            if ($subjectFk) {
                DB::statement('ALTER TABLE teacher_subjects DROP FOREIGN KEY ' . $subjectFk);
            }

            $indexes = collect(DB::select('SHOW INDEX FROM teacher_subjects'))
                ->pluck('Key_name')
                ->unique();

            if ($indexes->contains('teacher_subjects_user_subject_standard_unique')) {
                $table->dropUnique('teacher_subjects_user_subject_standard_unique');
            }

            if (! $indexes->contains('teacher_subjects_user_id_unique')) {
                $table->unique('user_id');
            }

            if (! $indexes->contains('teacher_subjects_user_id_subject_id_unique')) {
                $table->unique(['user_id', 'subject_id']);
            }
        });

        Schema::table('teacher_subjects', function (Blueprint $table) use ($userFk, $subjectFk) {
            if ($userFk) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            }

            if ($subjectFk) {
                $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            }
        });
    }
};
