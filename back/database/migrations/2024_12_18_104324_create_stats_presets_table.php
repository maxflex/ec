<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stats_presets', function (Blueprint $table) {
            $table->id();
            $table->string('metric');
            $table->string('label');
            $table->string('color');
            $table->json('filters');
            $table->timestamps();
        });
    }
};
