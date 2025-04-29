<?php

use App\Enums\LogDevice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table
                ->enum('device', array_column(LogDevice::cases(), 'value'))
                ->default(LogDevice::desktop->value)
                ->after('type')
                ->index();
        });

        DB::table('logs')
            ->where('is_mobile', true)
            ->update([
                'device' => LogDevice::mobile->value,
            ]);

        Schema::table('logs', function (Blueprint $table) {
            $table->dropColumn('is_mobile');
        });
    }
};
