<?php

use App\Enums\Program;
use App\Models\Client;
use App\Models\Teacher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scholarship_scores', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year')->unsigned();
            $table->smallInteger('month')->unsigned();
            $table->foreignIdFor(Client::class)->constrained();
            $table->foreignIdFor(Teacher::class)->constrained();
            $table->enum(
                'program',
                array_column(Program::cases(), 'name')
            )->index();
            $table->smallInteger('score')->unsigned();
            $table->timestamps();
        });
    }
};
