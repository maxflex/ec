<?php

use App\Models\ContractVersionProgram;
use App\Models\Group;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ContractVersionProgram::class)->constrained();
            $table->foreignIdFor(Group::class)->constrained();
            $table->unique(['contract_version_program_id', 'group_id']);
        });
        Schema::dropIfExists('group_contracts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_groups');
    }
};
