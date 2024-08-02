<?php

use App\Models\Contract;
use App\Models\Group;
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
        Schema::create('group_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Group::class)->constrained();
            $table->foreignIdFor(Contract::class)->constrained();
            $table->unique(['group_id', 'contract_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_contracts');
    }
};
