<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('table')->nullable()->index();
            $table->unsignedBigInteger('row_id')->nullable()->index();
            $table->json('data')->nullable();
            $table->enum('type', array_map(fn ($e) => $e->name, App\Enums\LogType::cases()));
            $table->foreignIdFor(User::class)->nullable()->constrained();
            $table->string('ip', 15)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
};
