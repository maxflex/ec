<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\User::class)->nullable()->after('entity_id')->constrained();
        });
    }
};
