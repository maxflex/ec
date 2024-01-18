<?php

use App\Enums\Grade;
use App\Enums\RequestStatus;
use App\Models\Client;
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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            // $table->foreignIdFor(Client::class)->constrained();
            $table->unsignedBigInteger('responsible_user_id')->nullable()->constrained();
            $table->foreign('responsible_user_id')->references('id')->on('users');
            $table->enum(
                'status',
                collect(RequestStatus::cases())->map(fn ($e) => $e->name)->all()
            )->default(RequestStatus::new->name)->index();
            $table->enum(
                'grade',
                collect(Grade::cases())->map(fn ($e) => $e->name)->all()
            )->nullable();
            $table->string('google_id')->nullable()->index();
            $table->string('yandex_id')->nullable()->index();
            $table->string('ip')->nullable();
            $table->string('comment', 1000)->nullable();
            $table->foreignIdFor(User::class)->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
