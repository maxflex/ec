<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_drafts', function (Blueprint $table) {
            $table->dropForeign('schedule_drafts_contract_id_foreign');
            $table->dropColumn('contract_id');
        });
    }
};
