<?php

namespace App\Console\Commands\Once;

use App\Enums\RequestStatus;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateEnumCommand extends Command
{
    protected $signature = 'once:update-enum';

    protected $description = 'Command description';

    public function handle(): void
    {
        $table = 'requests';
        $field = 'status';
        $enums = RequestStatus::cases();

        Schema::table($table, function (Blueprint $table) {
            $table->string('new_enum');
        });

        DB::table($table)->update([
            'new_enum' => DB::raw($field),
        ]);

        // обновление значений
        DB::table($table)
            ->where($field, 'awaiting')
            ->update([
                'new_enum' => RequestStatus::waiting->value,
            ]);

        DB::table($table)
            ->where($field, 'trash')
            ->update([
                'new_enum' => RequestStatus::refused->value,
            ]);
        // конец обновление значений

        Schema::table($table, function (Blueprint $table) use ($field, $enums) {
            $table->dropColumn($field);
            $table->enum($field, array_column($enums, 'value'))
                ->default(RequestStatus::new)
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
