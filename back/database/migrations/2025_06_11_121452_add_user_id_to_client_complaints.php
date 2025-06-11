<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_complaints', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\User::class)->nullable()->after('text')->constrained();
        });

        \Illuminate\Support\Facades\DB::table('client_complaints')->update(['user_id' => 209]);
    }
};
