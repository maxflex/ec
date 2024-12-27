<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Exam;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Добавление после основного переноса
 */
class TransferAfterExamScores extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:after:exam-scores';
    protected $description = 'Transfer after exam scores';

    public function handle()
    {
        DB::table('exam_scores')->delete();

        $items = DB::connection('egecrm')
            ->table('reviews')
            ->where('score', '>', 0)
            ->where('max_score', '>', 0)
            ->selectRaw("
                reviews.*, (
                    select created_email_id 
                    from review_comments as rc
                    where rc.review_id = reviews.id
                    order by rc.id desc
                    limit 1
                ) as created_email_id
            ")
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $r) {
            $userId = $this->getUserId($r->created_email_id);
            DB::table('exam_scores')->insert([
                'client_id' => $r->client_id,
                'year' => $r->year,
                'exam' => $this->getExam($r)->value,
                'score' => $r->score,
                'max_score' => $r->max_score,
                'user_id' => null,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }


    /**
     * есть несколько записей, которые нужно обработать
     * 12/сочинение - 2
     * 26/МАТ ПРАКТИКУМ – 7
     * 27/ОБЩ ПРАКТИКУМ – 5
     */
    private function getExam($r): Exam
    {
        $subject = match ($r->subject_id) {
            default => 'Math',
            2 => 'Phys',
            3 => 'Chem',
            4 => 'Bio',
            5 => 'Inf',
            6 => 'Rus',
            7 => 'Lit',
            8 => 'Soc',
            9 => 'His',
            10 => 'Eng',
            11 => 'Geo'
        };

        switch (intval($r->max_score)) {
            case 5:
                $prefix = 'oge';
                break;

            case 100:
                $prefix = 'ege';
                if ($subject === 'Math') {
                    $subject = 'MathProf';
                }
                break;

            // case 20:
            default:
                return Exam::egeMathBase;
        }

        return Exam::from($prefix . $subject);
    }
}
