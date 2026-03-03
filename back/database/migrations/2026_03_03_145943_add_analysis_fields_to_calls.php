<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->text('analysis_3')->nullable()->after('summary');
            $table->text('analysis_2')->nullable()->after('summary');
            $table->text('analysis_1')->nullable()->after('summary');
        });
    }
};
