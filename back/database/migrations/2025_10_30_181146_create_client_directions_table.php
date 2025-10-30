<?php

use App\Enums\Direction;
use App\Models\Client;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_directions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained('clients');
            $table->unsignedSmallInteger('year');
            $table->enum('direction', array_column(Direction::cases(), 'value'));
            $table->unique(['client_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_directions');
    }
};
