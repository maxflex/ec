<?php

namespace App\Models;

use App\Traits\HasComments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherComplaint extends Model
{
    use HasComments;

    protected $fillable = [
        'text', 'status',
    ];

    /**
     * @return BelongsTo<Teacher>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
