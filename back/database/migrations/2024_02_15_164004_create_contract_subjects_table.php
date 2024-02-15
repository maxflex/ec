<?php

use App\Enums\Subject;
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
        Schema::create('contract_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ContractVersion::class);
            $table->enum(
                'subject',
                collect(Subject::cases())->map(fn ($e) => $e->name)->all()
            );
            $table->unsignedTinyInteger('lessons');
            $table->unsignedTinyInteger('lessons_planned');
            $table->unsignedSmallInteger('price');
            $table->boolean('is_closed')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_subjects');
    }
};
