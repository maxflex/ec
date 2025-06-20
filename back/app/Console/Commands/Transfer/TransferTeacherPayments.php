<?php

namespace App\Console\Commands\Transfer;

use App\Enums\TeacherPaymentMethod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferTeacherPayments extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:teacher-payments';
    protected $description = 'Transfer teacher-payments';

    public function handle()
    {
        DB::table('teacher_payments')->delete();
        $items = DB::connection('egecrm')
            ->table('payments')
            ->where('entity_type', self::ET_TEACHER)
            ->where('category', '<>', 'lunch')
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $p) {
            DB::table('teacher_payments')->insert([
                'sum' => $p->sum,
                'date' => $p->date,
                'year' => $p->year,
                'method' => TeacherPaymentMethod::fromOld($p->method)->name,
                'teacher_id' => $p->entity_id,
                'purpose' => match ($p->category) {
                    'ege_trial' => 'Пробник',
                    'career_guidance' => 'Профориентация',
                    default => 'Обучение'
                },
                'is_confirmed' => $p->is_confirmed,
                'user_id' => $this->getUserId($p->created_email_id),
                'created_at' => $p->created_at,
                'updated_at' => $p->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}
