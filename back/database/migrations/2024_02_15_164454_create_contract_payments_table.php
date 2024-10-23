<?php

use App\Enums\ContractPaymentMethod;
use App\Models\Contract;
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
        Schema::create('contract_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Contract::class)->constrained();
            $table->unsignedInteger('sum');
            $table->date('date')->index();
            $table->enum(
                'method',
                array_column(ContractPaymentMethod::cases(), 'name')
            );
            $table->boolean('is_confirmed')->default(false)->index();
            $table->boolean('is_return')->default(false)->index();
            $table->string('card_number')->nullable();
            $table->unsignedInteger('pko_number')->nullable();
            $table->foreignIdFor(User::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_payments');
    }
};
