<?php

namespace App\Console\Commands;

use App\Enums\CallerType;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class UpdateEnumsCommand extends Command
{
    protected $signature = 'app:update-enums';

    protected $description = 'Update enum values for table';

    public function handle(): void
    {
        /** @var object{enums: UnitEnum[], table: string, field: string, after: ?string, index: bool, nullable: bool} $settings */
        $settings = (object) [
            // enums
            'enums' => CallerType::cases(),

            // таблица с enum-полем
            'table' => 'calls',

            // название enum-поля в таблице
            'field' => 'caller_type',

            // место enum-поля в таблице
            'after' => 'instruction',

            // сделать поле index?
            'index' => false,

            'nullable' => true,
        ];

        Schema::table($settings->table, function (Blueprint $table) use ($settings) {
            $table->string('new_enum')->nullable($settings->nullable);
        });

        DB::table($settings->table)->update([
            'new_enum' => DB::raw($settings->field),
        ]);

        // обновление значений (только если есть перестановки, т.е. не просто добавление)
        // DB::table($settings->table)
        //     ->where($settings->field, 'awaiting')
        //     ->update([
        //         'new_enum' => RequestStatus::waiting->value,
        //     ]);
        //
        // DB::table($settings->table)
        //     ->where($settings->field, 'trash')
        //     ->update([
        //         'new_enum' => RequestStatus::refused->value,
        //     ]);
        // конец обновление значений

        Schema::table($settings->table, function (Blueprint $table) use ($settings) {
            $table->dropColumn($settings->field);

            $upd = $table
                ->enum($settings->field, array_column($settings->enums, 'value'))
                ->nullable($settings->nullable);

            if ($settings->after) {
                $upd->after($settings->after);
            }

            if ($settings->index) {
                $upd->index();
            }
        });

        DB::table($settings->table)->update([
            $settings->field => DB::raw('new_enum'),
        ]);

        Schema::table($settings->table, function (Blueprint $table) {
            $table->dropColumn('new_enum');
        });
    }
}
