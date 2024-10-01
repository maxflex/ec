<?php

use App\Enums\TelegramTemplate;
use App\Models\Phone;
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
        Schema::dropIfExists('telegram_messages');
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->mediumText('text');
            $table->foreignIdFor(Phone::class)->constrained();
            $table->foreignId('list_id')->nullable();
            $table->foreign('list_id')->references('id')->on('telegram_lists');
            $table->enum(
                'template',
                collect(TelegramTemplate::cases())->map(fn($e) => $e->name)->all()
            )->nullable()->default(null);
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
