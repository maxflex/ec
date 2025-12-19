<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('other_payments', function (Blueprint $table) {
            $table->ipAddress('receipt_ip')->nullable()->after('is_return');
            $table->string('receipt_number')->nullable()->after('is_return');
        });
    }
};
