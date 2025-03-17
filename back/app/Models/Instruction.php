<?php

namespace App\Models;

use App\Observers\InstructionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Jfcherng\Diff\Differ;
use Jfcherng\Diff\DiffHelper;
use tidy;

#[ObservedBy(InstructionObserver::class)]
class Instruction extends Model
{
    protected $fillable = [
        'title', 'text', 'entry_id', 'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function scopeWithLastVersionsCte($query, bool $onlyPublished = true)
    {
        $lastVersionsCte = self::selectRaw('
            entry_id as max_entry_id,
            MAX(id) as max_id
        ')->groupBy('entry_id');

        if ($onlyPublished) {
            $lastVersionsCte->where('is_published', true);
        }

        $query->withExpression('last_versions', $lastVersionsCte);
    }

    public function scopeQueryForTeacher($query, $teacherId)
    {
        $query
            ->withLastVersionsCte()
            ->leftJoin(
                'last_versions',
                fn ($join) => $join
                    ->on('last_versions.max_id', '=', 'instructions.id')
                    ->on('last_versions.max_entry_id', '=', 'instructions.entry_id')
            )
            ->leftJoin(
                'instruction_signs',
                fn ($join) => $join
                    ->on('instruction_signs.instruction_id', '=', 'instructions.id')
                    ->where('instruction_signs.teacher_id', $teacherId)
            )
            ->whereRaw(<<<'SQL'
                (instruction_signs.id IS NOT NULL OR last_versions.max_id IS NOT NULL)
            SQL)
            ->selectRaw('instructions.*, signed_at')
            ->orderByRaw(<<<'SQL'
                if(signed_at is null, 0, 1) asc,
                signed_at desc,
                instructions.created_at desc
            SQL);
    }

    public function scopeLastVersions($query, bool $onlyPublished = true)
    {
        $query
            ->withLastVersionsCte($onlyPublished)
            ->join(
                'last_versions',
                fn ($join) => $join
                    ->on('last_versions.max_id', '=', 'instructions.id')
                    ->on('last_versions.max_entry_id', '=', 'instructions.entry_id')
            );
    }

    public function scopePublished($query)
    {
        $query->where('is_published', true);
    }

    public function getDiff($teacherId = null)
    {
        $prev = $this->versions()
            ->where('created_at', '<', $this->created_at)
            ->when(
                $teacherId,
                fn ($q) => $q->whereHas(
                    'signs',
                    fn ($q) => $q->where('teacher_id', $teacherId)
                )
            )
            ->latest()
            ->first();

        if ($prev === null) {
            return null;
        }

        return [
            'current' => extract_fields($this, [
                'index', 'title',
            ]),
            'prev' => extract_fields($prev, [
                'index', 'title',
            ]),
            'diff' => DiffHelper::calculate(
                $prev->getTidyText(),
                $this->getTidyText(),
                'SideBySide',
                ['context' => Differ::CONTEXT_ALL],
                ['showHeader' => false]
            ),
        ];
    }

    public function versions()
    {
        return $this->hasMany(self::class, 'entry_id', 'entry_id');
    }

    private function getTidyText()
    {
        $allowedTags = ['a', 'li', 'img'];
        $result = preg_replace('/<li>/', '<br>– ', $this->text);
        $result = preg_replace("/<\/li>/", '', $result);
        $tidy = new tidy;
        $tidy->parseString($result, [
            'indent' => true,
            'output-xhtml' => false,
            'show-body-only' => true,
            'wrap' => 1000,
        ], 'utf8');
        $tidy->cleanRepair();
        $result = strip_tags((string) $tidy, $allowedTags);
        $result = preg_replace("/[\r\n]+/", "\n", $result);
        $result = preg_replace('/&gt;/', '>', $result);
        $result = preg_replace('/&lt;/', '<', $result);

        return $result;
    }

    /**
     * Номер версии
     */
    public function getIndexAttribute()
    {
        return $this->versions()
            ->where('id', '<=', $this->id)
            ->count();
    }

    /**
     * Используется преподами
     */
    public function getIsLastVersionAttribute()
    {
        return ! $this->versions()
            ->published()
            ->where('id', '>', $this->id)
            ->exists();
    }

    /**
     * Используется преподами
     */
    public function getIsFirstVersion(int $teacherId)
    {
        return ! Instruction::queryForTeacher($teacherId)
            ->where('entry_id', $this->entry_id)
            ->where('instructions.id', '<', $this->id)
            ->exists();
    }

    /**
     * Архив если не подписано и не последняя версия
     */
    public function isArchive(int $teacherId): bool
    {
        return ! $this->is_last_version && $this->getSignedAt($teacherId) === null;
    }

    public function getSignedAt(int $teacherId)
    {
        return $this->signs()->where('teacher_id', $teacherId)->value('signed_at');
    }

    public function signs()
    {
        return $this->hasMany(InstructionSign::class);
    }
}
