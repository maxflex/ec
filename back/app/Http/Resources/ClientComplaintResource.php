<?php

namespace App\Http\Resources;

use App\Models\ClientComplaint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ClientComplaint */ class ClientComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'text', 'client_id', 'teacher_id', 'program',
        ]);
    }
}
