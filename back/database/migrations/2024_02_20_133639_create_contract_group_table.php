<?php

use App\Models\Contract;
use App\Models\Group;
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
        Schema::create('contract_group', function (Blueprint $table) {
            $table->foreignIdFor(Contract::class)->constrained();
            $table->foreignIdFor(Group::class)->constrained();
            $table->unique(['contract_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_group');
    }
};
