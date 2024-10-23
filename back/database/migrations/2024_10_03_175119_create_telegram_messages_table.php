<?php

use App\Enums\TelegramTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
            $table->mediumText('text');
            $table->foreignId('list_id')->nullable();
            $table->foreign('list_id')->references('id')->on('telegram_lists');
            $table->string('number');
            $table->unsignedBigInteger('telegram_id')->nullable();
            $table->morphs('entity');
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
