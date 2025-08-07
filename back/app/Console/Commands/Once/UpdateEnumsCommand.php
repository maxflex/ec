<?php

namespace App\Console\Commands\Once;

use App\Enums\Cabinet;
use App\Enums\RequestStatus;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateEnumsCommand extends Command
{
    protected $signature = 'once:update-enums';

    protected $description = 'Update enum values for table';

    public function handle(): void
    {
        $table = 'lessons';
        $field = 'cabinet';
        $enums = Cabinet::cases();

        Schema::table($table, function (Blueprint $table) {
            $table->string('new_enum');
        });

        DB::table($table)->update([
            'new_enum' => DB::raw($field),
        ]);

        // обновление значений
        // DB::table($table)
        //     ->where($field, 'awaiting')
        //     ->update([
        //         'new_enum' => RequestStatus::waiting->value,
        //     ]);
        //
        // DB::table($table)
        //     ->where($field, 'trash')
        //     ->update([
        //         'new_enum' => RequestStatus::refused->value,
        //     ]);
        // конец обновление значений

        Schema::table($table, function (Blueprint $table) use ($field, $enums) {
            $table->dropColumn($field);
            $table->enum($field, array_column($enums, 'value'))
                ->default($enums[0]->value)
                ->after('status')
                ->index();
        });

        DB::table($table)->update([
            $field => DB::raw('new_enum'),
        ]);

        Schema::table($table, function (Blueprint $table) {
            $table->dropColumn('new_enum');
        });
    }
}
