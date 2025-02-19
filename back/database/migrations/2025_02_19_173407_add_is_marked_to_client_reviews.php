<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_reviews', function (Blueprint $table) {
            $table->boolean('is_marked')->default(false)->index();
        });
    }
};
