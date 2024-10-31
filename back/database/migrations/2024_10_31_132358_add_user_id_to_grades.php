<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\User::class)->nullable()->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            //
        });
    }
};
