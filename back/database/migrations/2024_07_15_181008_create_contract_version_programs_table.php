<?php

use App\Enums\Program;
use App\Models\ContractVersion;
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
        Schema::create('contract_version_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ContractVersion::class)->constrained();
            $table->enum(
                'program',
                array_column(Program::cases(), 'name')
            );
            $table->unsignedTinyInteger('lessons_planned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_version_programs');
    }
};
