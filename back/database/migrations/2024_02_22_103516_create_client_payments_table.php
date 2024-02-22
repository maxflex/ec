<?php

use App\Enums\ClientPaymentMethod;
use App\Enums\CompanyType;
use App\Enums\PaymentType;
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
        Schema::create('client_payments', function (Blueprint $table) {
            $table->id();
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
                'type',
                collect(PaymentType::cases())->map(
                    fn ($e) => $e->name
                )->all()
            )->default(PaymentType::payment->name);
            $table->enum(
                'company',
                collect(CompanyType::cases())->map(fn ($e) => $e->name)->all()
            );
            $table->boolean('is_confirmed')->default(false)->index();
            // $table->boolean('is_return')->default(false)->index();
            $table->morphs('entity');
            $table->string('purpose')->nullable();
            $table->string('extra')->nullable();
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
