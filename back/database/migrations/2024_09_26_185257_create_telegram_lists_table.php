<?php

use App\Enums\SendTo;
use App\Enums\TelegramListStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('telegram_lists', function (Blueprint $table) {
            $table->id();
            $table->string('text', 1000);
            $table->enum(
                'status',
                array_column(TelegramListStatus::cases(), 'name')
            )
                ->default(TelegramListStatus::scheduled->name)
                ->index();
            $table->enum(
                'send_to',
                collect(SendTo::cases())->map(fn($e) => $e->name)->all()
            );
            $table->boolean('is_confirmable')->default(false);
            $table->foreignIdFor(\App\Models\Event::class)->nullable()->constrained();
            $table->dateTime('scheduled_at')->nullable();
            $table->json('recipients')->nullable();
            $table->foreignIdFor(\App\Models\User::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_lists');
    }
};
