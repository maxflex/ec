<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_payments', function (Blueprint $table) {
            // Переименовываем
            $table->renameColumn('sbp_id', 'external_id');
        });

        Schema::table('contract_payments', function (Blueprint $table) {
            // Меняем тип
            $table->string('external_id', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('contract_payments', function (Blueprint $table) {
            // Возвращаем тип обратно
            $table->char('external_id', 36)->nullable()->change();
        });

        Schema::table('contract_payments', function (Blueprint $table) {
            // Возвращаем старое имя
            $table->renameColumn('external_id', 'sbp_id');
        });
    }
};
