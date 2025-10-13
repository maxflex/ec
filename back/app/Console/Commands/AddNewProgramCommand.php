<?php

namespace App\Console\Commands;

use App\Enums\Program;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddNewProgramCommand extends Command
{
    protected $signature = 'app:add-new-program {--dry}';

    protected $description = 'Добавить новую программу';

    public function handle(): void
    {
        /**
         * SELECT
         * TABLE_SCHEMA,
         * TABLE_NAME,
         * COLUMN_NAME,
         * COLUMN_TYPE
         * FROM INFORMATION_SCHEMA.COLUMNS
         * WHERE COLUMN_NAME = 'program' AND DATA_TYPE = 'enum' AND TABLE_SCHEMA = DATABASE();
         */
        $tables = [
            'client_reviews',
            'complaints',
            'contract_version_programs',
            'grades',
            'groups',
            'reports',
            'web_review_programs',
        ];

        $enums = collect(Program::cases())
            ->map(fn (Program $p) => "'".$p->value."'")
            ->implode(',');

        foreach ($tables as $table) {
            $sql = "ALTER TABLE `{$table}` MODIFY COLUMN `program` ENUM({$enums}) NOT NULL";

            if ($this->option('dry')) {
                $this->line($sql.';');
            } else {
                DB::statement($sql);
                $this->info("{$table} updated.");
            }
        }

        if ($this->option('dry')) {
            $this->comment('Dry run complete. Execute without --dry to apply.');
        }

    }
}
