<?php

namespace App\Console\Commands;

use App\Enums\Company;
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
        /** @var object{enums: UnitEnum[], table: string, field: string, after: ?string, index: bool} $settings */
        $settings = (object) [
            // enums
            'enums' => Company::cases(),

            // таблица с enum-полем
            'table' => 'contracts',

            // название enum-поля в таблице
            'field' => 'company',

            // место enum-поля в таблице
            'after' => 'year',

            // сделать поле index?
            'index' => true,
        ];

        Schema::table($settings->table, function (Blueprint $table) {
            $table->string('new_enum');
        });

        DB::table($settings->table)->update([
            'new_enum' => DB::raw($settings->field),
        ]);

        // обновление значений
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

            $upd = $table->enum($settings->field, array_column($settings->enums, 'value'));

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
