<?php

use App\Enums\ClientPaymentMethod;
use App\Enums\Company;
use App\Models\Client;
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
        Schema::dropIfExists('client_services');
        Schema::dropIfExists('client_payments');
        Schema::create('client_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained();
            $table->unsignedInteger('sum');
            $table->date('date')->index();
            $table->unsignedSmallInteger('year')->index();
            $table->enum(
                'method',
                collect(ClientPaymentMethod::cases())->map(
                    fn ($e) => $e->name
                )->all()
            );
            $table->enum(
                'company',
                collect(Company::cases())->map(fn ($e) => $e->name)->all()
            );
            $table->string('purpose')->nullable();
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
        Schema::dropIfExists('client_payments');
    }
};
