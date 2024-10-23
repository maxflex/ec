<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Program;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferReviews extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:reviews';
    protected $description = 'Transfer reviews';

    public function handle()
    {
        DB::table('client_reviews')->delete();
        DB::table('web_reviews')->delete();
        $reviews = DB::connection('egecrm')
            ->table('reviews as r')
            ->join('review_comments as rc', 'rc.review_id', '=', 'r.id')
            ->where('rc.type', 'final')
            ->where('rc.rating', '>', 0)
            ->where('r.grade_id', '<>', 20) // пропускаем онлайн
            ->whereNotNull('rc.text')
            ->whereNotNull('rc.created_email_id')
            ->whereNotNull('r.score')
            ->selectRaw("
                r.client_id, r.subject_id, r.grade_id, r.signature, r.score, r.max_score,
                r.is_published, r.teacher_id, rc.text, rc.created_email_id, rc.rating,
                rc.created_at, rc.updated_at
            ")
            ->get();
        $bar = $this->output->createProgressBar($reviews->count());
        foreach ($reviews as $r) {
            $program = Program::fromOld($r->grade_id, $r->subject_id)->name;
            $userId = $this->getUserId($r->created_email_id);
            DB::table('client_reviews')->insert([
                'client_id' => $r->client_id,
                'teacher_id' => $r->teacher_id,
                'program' => $program,
                'text' => $r->text,
                'rating' => $r->rating,
                'user_id' => $userId,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ]);
            DB::table('web_reviews')->insertGetId([
                'client_id' => $r->client_id,
                'text' => $r->text,
                'signature' => $r->signature,
                'rating' => $r->rating,
                'is_published' => $r->is_published,
                'user_id' => $this->getUserId($r->created_email_id),
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}
