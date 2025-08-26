<?php

use App\Enums\OtherPaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_payments', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->unsignedInteger('sum');
            $table->date('date')->index();
            $table->enum(
                'method',
                array_column(OtherPaymentMethod::cases(), 'name')
            );
            $table->string('purpose');
            $table->boolean('is_confirmed')->default(false)->index();
            $table->boolean('is_return')->default(false)->index();
            $table->string('card_number')->nullable();
            $table->unsignedInteger('pko_number')->nullable();
            $table->foreignIdFor(\App\Models\User::class)->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_payments');
    }
};
