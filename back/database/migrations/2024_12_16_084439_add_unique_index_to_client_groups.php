<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('client_groups', function (Blueprint $table) {
            $table->unique(['group_id', 'contract_version_program_id']);
        });
    }
};
