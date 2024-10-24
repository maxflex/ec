<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Program;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Что-то нужно сделать с grade_id = 20 (онлайн)
 * В программу не конвертируется
 */
class TransferReviews extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:reviews';
    protected $description = 'Transfer reviews';

    public function handle()
    {
        DB::table('client_reviews')->delete();
        DB::table('web_reviews')->delete();

        $reviews = $this->getReviewsQuery()->where('rc.type', 'final')->get();
        $reviews = $reviews->merge(
            $this->getReviewsQuery()
                ->whereIn('r.id', $this->getHasAdminButNoFinal())
                ->where('rc.type', 'admin')
                ->get()
        );

        $bar = $this->output->createProgressBar($reviews->count());
        foreach ($reviews as $r) {
            $program = Program::fromOld($r->grade_id, $r->subject_id)->name;
            $userId = $this->getUserId($r->created_email_id);
            DB::table('client_reviews')->insert([
                'id' => $r->id,
                'client_id' => $r->client_id,
                'teacher_id' => $r->teacher_id,
                'program' => $program,
                'text' => $r->text,
                'rating' => $r->rating,
                'user_id' => $userId,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ]);
            if (
                $r->type === 'admin'
                && $r->signature
                && $r->rating
            ) {
                DB::table('web_reviews')->insert([
                    'id' => $r->id,
                    'client_id' => $r->client_id,
                    'text' => $r->text,
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
            ->where('rc.rating', '>', 0)
            ->where('r.grade_id', '<>', 20) // пропускаем онлайн
            ->whereNotNull('rc.text')
            ->whereNotNull('rc.created_email_id')
            ->whereNotNull('r.score')
            ->selectRaw("
                r.client_id, r.subject_id, r.grade_id, r.signature, r.score, r.max_score,
                r.teacher_id, rc.text, rc.created_email_id, rc.rating,
                rc.created_at, rc.updated_at, r.id, rc.type
            ");
    }

    /**
     * Отзывы, у которых есть тип "admin" но нет типа "final"
     * Их нужно перенести, когда нет final
     */
    private function getHasAdminButNoFinal()
    {
        $hasAdminButNoFinal = DB::connection('egecrm')->select("
            select id 
            from reviews r
            where exists (
             select 1 from review_comments rc where rc.review_id = r.id and `type` = 'admin'
            ) and not exists (
             select 1 from review_comments rc where rc.review_id = r.id and `type` = 'final'
            )
        ");

        return array_column($hasAdminButNoFinal, 'id');
    }
}
