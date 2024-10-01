<?php

namespace App\Models;

use App\Traits\HasName;
use App\Traits\HasPhones;
use App\Traits\HasPhoto;
use App\Traits\RelationSyncable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasName, HasPhones, HasPhoto, RelationSyncable;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name',
        'is_active', 'is_call_notifications'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_call_notifications' => 'boolean',
    ];

    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    public function clientTests(): HasMany
    {
        return $this->hasMany(ClientTest::class);
    }

    public function webReviews(): HasMany
    {
        return $this->hasMany(WebReview::class);
    }

    public function instructions(): HasMany
    {
        return $this->hasMany(Instruction::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function telegramMessages(): HasMany
    {
        return $this->hasMany(TelegramMessage::class);
    }

    public function examScores(): HasMany
    {
        return $this->hasMany(ExamScore::class);
    }

    public function contractVersions(): HasMany
    {
        return $this->hasMany(ContractVersion::class);
    }

    public function clientPayments(): HasMany
    {
        return $this->hasMany(ClientPayment::class);
    }

    public function contractPayments(): HasMany
    {
        return $this->hasMany(ContractPayment::class);
    }

    public function teacherPayments(): HasMany
    {
        return $this->hasMany(TeacherPayment::class);
    }

    public function teacherServices(): HasMany
    {
        return $this->hasMany(TeacherService::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function tests(): HasMany
    {
        return $this->hasMany(Test::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function telegramLists(): HasMany
    {
        return $this->hasMany(TelegramList::class);
    }
}
