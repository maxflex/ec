<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_payments', function (Blueprint $table) {
            $table->renameColumn('receipt_sent_to', 'receipt_number');
        });
        Schema::table('contract_payments', function (Blueprint $table) {
            $table->ipAddress('receipt_ip')->nullable()->after('receipt_number');
        });
    }
};
