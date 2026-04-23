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
        // Convert numeric term values to word-based values
        DB::statement("UPDATE student_results SET term = 'term_one' WHERE term = '1'");
        DB::statement("UPDATE student_results SET term = 'term_two' WHERE term = '2'");
        DB::statement("UPDATE student_results SET term = 'term_three' WHERE term = '3'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to numeric values
        DB::statement("UPDATE student_results SET term = '1' WHERE term = 'term_one'");
        DB::statement("UPDATE student_results SET term = '2' WHERE term = 'term_two'");
        DB::statement("UPDATE student_results SET term = '3' WHERE term = 'term_three'");
    }
};
