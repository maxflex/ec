<?php

namespace App\Http\Resources;

use App\Enums\Program;
use App\Models\Client;
use App\Models\ClientReview;
use App\Models\ExamScore;
use App\Models\Teacher;
use App\Models\TelegramMessage;
use App\Utils\ClientReviewMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ClientReview
 */
class ClientReviewListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $programEnum = $this->program instanceof Program ? $this->program : Program::tryFrom($this->program);

        $lessonsCountAndYears = ClientReview::getLessonsCountAndYears(
            $this->client_id,
            $this->teacher_id,
            $programEnum,
        );

        $fakeId = collect([
            $this->client_id,
            $this->teacher_id,
            $this->program,
        ])->join('-');

        $id = $this->id ?? $fakeId;

        $teacher = Teacher::find($this->teacher_id);
        $client = Client::find($this->client_id);

        return extract_fields($this, ['*'], [
            ...$lessonsCountAndYears,
            'id' => $id,
            'ttl' => ClientReviewMessage::getTtl($client, $id),
            'is_marked' => (bool) $this->is_marked,
            'teacher' => new PersonResource($teacher),
            'client' => new PersonResource($client),
            'telegram_message' => TelegramMessage::where('extra', $fakeId)->latest()->first()?->text,
            'exam_scores' => ExamScore::where('client_id', $this->client_id)->select([
                'id', 'score', 'max_score', 'exam',
            ])->get(),
        ]);
    }
}
