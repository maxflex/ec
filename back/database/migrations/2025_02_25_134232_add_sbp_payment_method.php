<?php

use App\Enums\ContractPaymentMethod;
use App\Enums\OtherPaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['client_payments', 'contract_payments'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('method_new');
            });

            DB::table($table)->update([
                'method_new' => DB::raw('method'),
            ]);

            // обновление значений
            DB::table($table)
                ->where('card_number', 'СБП')
                ->update([
                    'card_number' => null,
                    'method_new' => 'sbp',
                ]);
            // конец обновление значений

            $cases = $table === 'client_payments' ? OtherPaymentMethod::cases() : ContractPaymentMethod::cases();

            Schema::table($table, function (Blueprint $table) use ($cases) {
                $table->dropColumn('method');
                $table->enum('method', array_column($cases, 'value'))
                    ->after('date')
                    ->index();
            });

            DB::table($table)->update([
                'method' => DB::raw('method_new'),
            ]);

            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('method_new');
            });

            DB::update("
               UPDATE $table
               SET card_number = REGEXP_REPLACE(card_number, '[^0-9]', '');
            ");
        }
    }
};
