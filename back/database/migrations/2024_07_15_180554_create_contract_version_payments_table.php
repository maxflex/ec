<?php

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
        Schema::dropIfExists('contract_payments');
        Schema::create('contract_version_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ContractVersion::class)->constrained();
            $table->unsignedInteger('sum');
            $table->date('date')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_version_payments');
    }
};
