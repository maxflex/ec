<?php

use App\Enums\SendTo;
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
        Schema::table('telegram_lists', function (Blueprint $table) {
            $table->dropColumn('send_to');
        });

        Schema::table('telegram_lists', function (Blueprint $table) {
            $table->set('send_to', array_column(SendTo::cases(), 'value'))->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_lists', function (Blueprint $table) {
            //
        });
    }
};
