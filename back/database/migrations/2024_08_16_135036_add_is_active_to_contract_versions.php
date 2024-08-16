<?php

use App\Models\ContractVersion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contract_versions', function (Blueprint $table) {
            $table->boolean('is_active')->after('contract_id')->default(false)->index();
        });
        ContractVersion::lastVersions()->update([
            'is_active' => true
        ]);
        Schema::table('contract_versions', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_versions', function (Blueprint $table) {
            //
        });
    }
};
