<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
//        Schema::create('web_reviews', function (Blueprint $table) {
//            $table->id();
//            $table->foreignIdFor(Client::class)->constrained();
//            $table->text('text');
//            $table->string('signature');
//            $table->unsignedSmallInteger('rating');
//            $table->foreignIdFor(User::class)->constrained();
//            $table->timestamps();
//        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_reviews');
    }
};
