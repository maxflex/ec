<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->string('id', 128)->primary();
            $table->foreignIdFor(User::class)->nullable()->index()->constrained();
            $table->enum('type', array_map(fn($e) => $e->name, App\Enums\CallType::cases()))->index();
            $table->string('number')->index();
            $table->string('line');
            $table->string('recording')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('answered_at')->nullable();
            $table->dateTime('finished_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
