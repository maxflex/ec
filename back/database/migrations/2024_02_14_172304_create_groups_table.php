<?php

use App\Enums\Program;
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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->enum(
                'program',
                array_column(Program::cases(), 'name')
            )->index();
            $table->unsignedSmallInteger('year')->index();
            $table->char('letter')->nullable();
            $table->date('contract_date')->nullable();
            $table->unsignedSmallInteger('lessons_planned')->nullable();
            $table->foreignIdFor(User::class)->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
