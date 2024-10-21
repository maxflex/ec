<?php

use App\Enums\Direction;
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
        DB::table('passes')->truncate();
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('requests');
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->nullable()->constrained();
            $table->unsignedBigInteger('responsible_user_id')->nullable();
            $table->foreign('responsible_user_id')->references('id')->on('users');
            $table->enum(
                'status',
                array_column(RequestStatus::cases(), 'name')
            )->default(RequestStatus::new->name)->index();
            $table->enum(
                'direction', array_column(Direction::cases(), 'value')
            )->nullable();
            $table->boolean('is_verified')->default(false)->index();
            $table->string('google_id')->nullable()->index();
            $table->string('yandex_id')->nullable()->index();
            $table->string('ip')->nullable();
            $table->foreignIdFor(User::class)->nullable()->constrained();
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
