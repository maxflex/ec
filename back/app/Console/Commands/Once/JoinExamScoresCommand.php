<?php

namespace App\Console\Commands\Once;

use App\Enums\Program;
use App\Models\ExamScore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class JoinExamScoresCommand extends Command
{
    protected $signature = 'once:join-exam-scores';

    protected $description = 'Command description';

    public function handle(): void
    {
        $this->clientLessons();
    }

    private function clientLessons(): void
    {
        $this->info('Loading client_lessons into memory...');

        $clientLessons = DB::table('client_lessons', 'cl')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->selectRaw('c.client_id, c.year, cvp.program, COUNT(*) as counted_lessons')
            ->groupByRaw('c.client_id, c.year, cvp.program')
            ->get();

        $csv = collect([[
            'client_id',
            'year',
            'program',
            'program_converted',
            'counted_lessons',
            'is_exam_match_found',
        ]]);
        $bar = $this->output->createProgressBar(count($clientLessons));
        foreach ($clientLessons as $cl) {
            $exam = Program::from($cl->program)->getExam();
            $examScoresCount = ExamScore::query()
                ->where('client_id', $cl->client_id)
                ->where('year', $cl->year)
                ->where('exam', Program::from($cl->program)->getExam())
                ->count();

            $csv->push([
                $cl->client_id,
                $cl->year,
                $cl->program,
                ($exam ? $exam->value : null),
                $cl->counted_lessons,
                $examScoresCount,
            ]);
            $bar->advance();
        }
        $bar->finish();

        $url = save_csv($csv);
        $this->info(PHP_EOL.$url);
    }

    private function examScores(): void
    {
        $examScores = DB::table('exam_scores')->get();
        $bar = $this->output->createProgressBar(count($examScores));
        $csv = collect([
            ['id', 'client_id', 'year', 'exam', 'score', 'max_score', 'user_id', 'created_at', 'updated_at', 'programs', 'exams'],
        ]);
        foreach ($examScores as $examScore) {
            $data = collect(DB::select("
                select distinct(cvp.program) as `program`
                from `client_lessons` cl
                join contract_version_programs as cvp on cvp.id = cl.contract_version_program_id
                join contract_versions as cv on cv.id = cvp.contract_version_id
                join contracts as c on c.id = cv.contract_id
                where c.client_id = {$examScore->client_id} and c.year = {$examScore->year}
            "));
            $programs = $data->pluck('program');
            $exams = collect();
            foreach ($programs as $program) {
                $exam = Program::from($program)->getExam();
                if ($exam !== null) {
                    $exams->push($exam->value);
                }
            }
            $csv->push([
                $examScore->id,
                $examScore->client_id,
                $examScore->year,
                $examScore->exam,
                $examScore->score,
                $examScore->max_score,
                $examScore->user_id,
                $examScore->created_at,
                $examScore->updated_at,
                $programs->join(', '),
                $exams->join(', '),
            ]);
            $bar->advance();
        }
        $bar->finish();

        $url = save_csv($csv);
        $this->info(PHP_EOL.$url);
    }
}
