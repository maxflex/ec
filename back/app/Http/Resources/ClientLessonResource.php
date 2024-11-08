<?php

namespace App\Http\Resources;

use App\Models\ClientLesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ClientLesson */
class ClientLessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'contract_version_program_id', 'minutes_late', 'status', 'scores',
        ], [
            'client' => new PersonWithPhotoResource(
                $this->contractVersionProgram->contractVersion->contract->client
            )
        ]);
    }
}
