<?php

namespace App\Console\Commands\Once;

use App\Enums\Program;
use App\Models\WebReview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Продублировать отзывы экстерната в старшую школу
 */
class DuplicateExternalReviewsCommand extends Command
{
    protected $signature = 'once:duplicate-external-reviews';

    protected $description = 'Command description';

    public function handle(): void
    {
        $webReviews = WebReview::query()
            ->whereHas('webReviewPrograms', fn ($q) => $q->whereIn('program', Program::getAllExternal()))
            ->get();

        foreach ($webReviews as $webReview) {
            $id = DB::table('web_reviews')->insertGetId([
                'client_id' => $webReview->client_id,
                'text' => $webReview->text,
                'signature' => $webReview->signature,
                'rating' => $webReview->rating,
                'is_published' => $webReview->is_published,
                'user_id' => $webReview->user_id,
                'created_at' => $webReview->created_at,
                'updated_at' => $webReview->updated_at,
            ]);
            foreach ($webReview->programs as $program) {
                $str = str($program->value)->replace('External', 'School11');
                $schoolProgram = Program::tryFrom($str);
                if (! $schoolProgram) {
                    throw new \Exception('Program '.$str.' not found');
                }
                DB::table('web_review_programs')->insert([
                    'web_review_id' => $id,
                    'program' => $str,
                ]);
            }
            $this->info($id);
        }
    }
}
