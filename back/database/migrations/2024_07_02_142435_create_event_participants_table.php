<?php

use App\Enums\EventParticipantConfirmation;
use App\Models\Event;
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
        Schema::create('event_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Event::class)->constrained();
            $table->enum('confirmation',
                collect(EventParticipantConfirmation::cases())->map(fn($e) => $e->value)->values()->all()
            )->default(EventParticipantConfirmation::pending);
            $table->morphs('entity');
            $table->unique(['event_id', 'entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_participants');
    }
};
