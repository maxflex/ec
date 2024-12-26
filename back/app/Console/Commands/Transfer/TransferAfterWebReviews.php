<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Что-то нужно сделать с grade_id = 20 (онлайн)
 * В программу не конвертируется
 */
class TransferAfterWebReviews extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:after:web-reviews';
    protected $description = 'Transfer web reviews';

    public function handle()
    {
        DB::table('web_reviews')->delete();

        $reviews = $this->getReviewsQuery()
            ->where('rc.type', 'final')
            ->where('r.is_published', 1)
            ->where('rc.rating', '>', 0)
            ->get();

        $bar = $this->output->createProgressBar($reviews->count());
        foreach ($reviews as $r) {
            $userId = $this->getUserId($r->created_email_id);
            // эти условия уже есть в query. тут на всякий случай
            if ($r->type === 'final' && $r->signature) {
                DB::table('web_reviews')->insert([
                    'id' => $r->id,
                    'client_id' => $r->client_id,
                    'text' => $r->text ?? '',
                    'signature' => $r->signature,
                    'rating' => $r->rating,
                    'user_id' => $userId,
                    'created_at' => $r->created_at,
                    'updated_at' => $r->updated_at,
                    // 24.10.2024 – осознанно убрали is_published из web_reviews
                    // 'is_published' => $r->is_published,
                ]);
            }
            $bar->advance();
        }
        $bar->finish();
    }

    private function getReviewsQuery()
    {
        return DB::connection('egecrm')
            ->table('reviews', 'r')
            ->join('review_comments as rc', 'rc.review_id', '=', 'r.id')
            ->selectRaw("
                r.client_id, r.subject_id, r.grade_id, r.signature, r.score, r.max_score,
                r.teacher_id, rc.text, rc.created_email_id, rc.rating,
                rc.created_at, rc.updated_at, r.id, rc.type, r.year
            ");
    }
}
