<?php

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
        Schema::create('web_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained();
            $table->text('text');
            $table->string('signature');
            $table->unsignedSmallInteger('rating');
            $table->boolean('is_published')->default(false);
            $table->foreignIdFor(User::class)->constrained();
            $table->timestamps();
        });
    }
    /**
id
текст
client_id
подпись
is_published
rating
user_id (кто создал)
created_at
updated_at

набранный балл 1
максимальный балл 1
программа 1
     */

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
