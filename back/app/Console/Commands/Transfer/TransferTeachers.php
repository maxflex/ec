<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Subject;
use App\Enums\TeacherStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferTeachers extends Command
{
    use TransferCommand;

    protected $signature = 'app:transfer:teachers';
    protected $description = 'Transfer teachers';

    public function handle()
    {
        DB::table('teachers')->delete();
        $teachers = DB::connection('egecrm')
            ->table('teachers')
            ->get();
        $bar = $this->output->createProgressBar($teachers->count());
        foreach ($teachers as $t) {
            $desc = join("\n\n", [
                $t->ready_to_work,
                $t->list_comment,
                $t->public_desc,
                $t->schedule,
                $t->impression,
                $t->students_category,
                $t->tutoring_experience,
                $t->experience,
                $t->preferences,
                $t->achievements,
                $t->education,
                $t->price,
                $t->contacts,
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
                'passport_series' => $this->nullify($t->passport_series),
                'passport_number' => $this->nullify($t->passport_number),
                'passport_address' => $this->nullify($t->passport_address),
                'passport_code' => $this->nullify($t->passport_code),
                'passport_issued_by' => $this->nullify($t->passport_issue_place),
                'so' => $t->so,
                'created_at' => $t->created_at,
                'updated_at' => $t->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}
