<?php

namespace App\Models;

use App\Enums\TeacherComplaintRecipient;
use App\Enums\TeacherComplaintStatus;
use App\Traits\HasComments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherComplaint extends Model
{
    use HasComments;

    protected $fillable = [
        'text', 'status', 'recipient',
    ];

    protected $casts = [
        'recipient' => TeacherComplaintRecipient::class,
        'status' => TeacherComplaintStatus::class,
    ];

    /**
     * @return BelongsTo<Teacher>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
