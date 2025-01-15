<?php

use App\Enums\Direction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->string('direction_new')->nullable();
        });

        DB::table('requests')->update([
            'direction_new' => DB::raw('direction')
        ]);

        DB::table('requests')
            ->whereIn('direction', ['online', 'python', 'english'])
            ->update([
                'direction_new' => Direction::coursesExtra->value,
            ]);

        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('direction');
            $table->enum(
                'direction', array_column(Direction::cases(), 'value')
            )->nullable()->index()->after('status');
        });

        DB::table('requests')->update([
            'direction' => DB::raw('direction_new')
        ]);

        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('direction_new');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
