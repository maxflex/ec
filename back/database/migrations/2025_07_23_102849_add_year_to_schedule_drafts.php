<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_drafts', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->after('contract_id');
        });
    }
};
