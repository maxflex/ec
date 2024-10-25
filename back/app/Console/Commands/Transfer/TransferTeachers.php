<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Subject;
use App\Enums\TeacherStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferTeachers extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:teachers';

    protected $description = 'Transfer teachers';

    public function handle()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('teachers')->delete();
        $teachers = DB::connection('egecrm')
            ->table('teachers')
            ->get();
        $bar = $this->output->createProgressBar($teachers->count());
        foreach ($teachers as $t) {
            $desc = implode("\n\n", [
                $t->comment,
                $t->description,
                $t->contacts,
                $t->education,
                $t->achievements,
                $t->preferences,
                $t->experience,
                $t->current_work,
                $t->tutoring_experience,
                $t->students_category,
                $t->impression,
            ]);
            $desc = trim(str_replace("\n\n\n\n", "\n\n", $desc), "\n\n");
            DB::table('teachers')->insert([
                'id' => $t->id,
                'first_name' => $t->first_name,
                'last_name' => $t->last_name,
                'middle_name' => $t->middle_name,
                'subjects' => $this->mapEnum($t->subjects_ec, Subject::class),
                'status' => $this->mapEnum($t->in_egecentr, TeacherStatus::class),
                'desc' => $this->nullify($desc),
                'photo_desc' => $this->nullify($t->photo_desc),

                'passport' => $t->passport_series ? json_encode([
                    'series' => $this->nullify($t->passport_series),
                    'number' => $this->nullify($t->passport_number),
                    'address' => $this->nullify($t->passport_address),
                    'code' => $this->nullify($t->passport_code),
                    'issued_by' => $this->nullify($t->passport_issue_place),
                ]) : null,
                'so' => $t->so,
                'created_at' => $t->created_at,
                'updated_at' => $t->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
        Schema::enableForeignKeyConstraints();
    }
}
