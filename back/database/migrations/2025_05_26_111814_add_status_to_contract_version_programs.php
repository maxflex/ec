<?php

use App\Enums\CvpStatus;
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
        Schema::table('contract_version_programs', function (Blueprint $table) {
            $table->enum('status', array_column(
                CvpStatus::cases(),
                'value'
            ))->index()->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_version_programs', function (Blueprint $table) {
            //
        });
    }
};
