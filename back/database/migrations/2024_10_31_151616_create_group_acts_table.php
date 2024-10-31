<?php

use App\Models\Group;
use App\Models\Teacher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_acts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Group::class)->constrained();
            $table->foreignIdFor(Teacher::class)->constrained();
            $table->date('date');
            $table->date('date_from');
            $table->date('date_to');
            $table->unsignedSmallInteger('lessons');
            $table->unsignedInteger('sum');
            $table->foreignIdFor(\App\Models\User::class)->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_acts');
    }
};
