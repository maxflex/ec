<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_payments', function (Blueprint $table) {
            $table->boolean('is_1c_synced')->default(false)->after('is_return');
        });
    }

    public function down(): void
    {
        Schema::table('contract_payments', function (Blueprint $table) {
            $table->dropColumn('is_1c_synced');
        });
    }
};
