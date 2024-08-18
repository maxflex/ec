<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contract_version_program_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_version_program_id'); // Foreign key column
            // Specifying a shorter name for the foreign key constraint
            $table->foreign('contract_version_program_id', 'contract_version_program_id_foreign')
                ->references('id')
                ->on('contract_version_programs');
            $table->unsignedSmallInteger('lessons');
            $table->unsignedSmallInteger('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_version_program_prices');
    }
};
