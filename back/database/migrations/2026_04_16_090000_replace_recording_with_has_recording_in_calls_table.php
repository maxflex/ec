<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->boolean('has_recording')->index()->default(false)->after('line');
        });

        // Переносим накопленные данные из legacy-колонки recording в bool-флаг.
        DB::table('calls')
            ->whereNotNull('recording')
            ->whereRaw("TRIM(recording) <> ''")
            ->update(['has_recording' => true]);

        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn('recording');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('calls', 'recording')) {
            Schema::table('calls', function (Blueprint $table) {
                $table->string('recording')->nullable()->after('line');
            });
        }

        if (Schema::hasColumn('calls', 'has_recording')) {
            Schema::table('calls', function (Blueprint $table) {
                $table->dropColumn('has_recording');
            });
        }
    }
};
