<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferHeadTeacherReports extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:head-teacher-reports';
    protected $description = 'Transfer headTeacherReports';

    public function handle()
    {
        DB::table('head_teacher_reports')->delete();
        $headTeacherReports = DB::connection('egecrm')
            ->table('head_teacher_reports')
            ->get()
            ->map(fn($headTeacherReport) => extract_fields($headTeacherReport, [
                'teacher_id', 'year', 'created_at', 'updated_at',
            ], [
                'text' => $headTeacherReport->name . ': ' . $headTeacherReport->text,
                'month' => $this->getMonth($headTeacherReport),
            ]))
            // сгруппировать по месяцам
            ->groupBy(fn($e) => implode("-", [
                $e['teacher_id'],
                $e['year'],
                $e['month']
            ]));

        $bar = $this->output->createProgressBar($headTeacherReports->count());
        foreach ($headTeacherReports as $data) {
            $insert = null;
            foreach ($data as $headTeacherReport) {
                if ($insert === null) {
                    $insert = $headTeacherReport;
                } else {
                    $insert['text'] .= (PHP_EOL . PHP_EOL . $headTeacherReport['text']);
                }
            }
            DB::table('head_teacher_reports')->insert($insert);
            $bar->advance();
        }
        $bar->finish();
    }

    private function getMonth($headTeacherReport): int
    {
        $name = str($headTeacherReport->name)->lower();

        if ($name->contains('май')) {
            return 5;
        }

        if ($name->contains(['апрель'])) {
            return 4;
        }

        if ($name->contains('март')) {
            return 3;
        }

        if ($name->contains('февраль')) {
            return 2;
        }

        if ($name->contains('январь')) {
            return 1;
        }

        if ($name->contains('декабрь')) {
            return 12;
        }

        if ($name->contains('ноябрь')) {
            return 11;
        }

        if ($name->contains('октябрь')) {
            return 10;
        }

        [, $month,] = explode('-', $headTeacherReport->date);
        return intval($month);
    }
}
