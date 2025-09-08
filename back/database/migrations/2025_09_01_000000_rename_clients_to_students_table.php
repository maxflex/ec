<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('clients', 'students');
        Schema::table('contracts', function (Blueprint $table) {
            $table->renameColumn('client_id', 'student_id');
        });
    }

    public function down(): void
    {
        Schema::rename('students', 'clients');
        Schema::table('contracts', function (Blueprint $table) {
            $table->renameColumn('student_id', 'client_id');
        });
    }
};
