<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\ClientParent::class)
                ->nullable()
                ->after('entity_id')
                ->constrained();

            $table->foreignIdFor(\App\Models\User::class, 'emulation_user_id')
                ->nullable()
                ->after('entity_id')
                ->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            //
        });
    }
};
