<?php

use App\Models\Phone;
use App\Models\User;
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
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->mediumText('text');
            // $table->morphs('entity');
            $table->foreignIdFor(Phone::class)->constrained();
            $table->unsignedInteger('entry_id')->nullable();
            $table->foreignIdFor(User::class)->nullable()->constrained()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};
