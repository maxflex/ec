<?php

namespace App\Models;

use App\Observers\InstructionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Jfcherng\Diff\{DiffHelper, Differ};

#[ObservedBy(InstructionObserver::class)]
class Instruction extends Model
{
    protected $fillable = [
        'title', 'text', 'entry_id'
    ];

    public function signs()
    {
        return $this->hasMany(InstructionSign::class);
    }

    public function versions()
    {
        return $this->hasMany(self::class, 'entry_id', 'entry_id');
    }

    public function scopeWithLastVersionsCte($query)
    {
        $lastVersionsCte = self::selectRaw(<<<SQL
            entry_id as max_entry_id,
            MAX(id) as max_id
        SQL)->groupBy('entry_id');

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
            ->whereRaw(<<<SQL
                (instruction_signs.id IS NOT NULL OR last_versions.max_id IS NOT NULL)
            SQL)
            ->selectRaw('instructions.*, signed_at')
            ->orderByRaw(<<<SQL
                if(signed_at is null, 0, 1) asc,
                signed_at desc,
                instructions.created_at desc
            SQL);
    }

    public function scopeLastVersions($query)
    {
        $query
            ->withLastVersionsCte()
            ->join(
                'last_versions',
                fn ($join) => $join
                    ->on('last_versions.max_id', '=', 'instructions.id')
                    ->on('last_versions.max_entry_id', '=', 'instructions.entry_id')
            );
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
            'current' => extract_fields($this, ['created_at', 'title']),
            'prev' => extract_fields($prev, ['created_at', 'title']),
            'diff' => $this->_getDiff($prev),
            'diff_all' => $this->_getDiff($prev, [
                'context' => Differ::CONTEXT_ALL
            ])
        ];
    }

    private function _getDiff(Instruction $prev, $options = [])
    {
        return DiffHelper::calculate(
            $prev->getTidyText(),
            $this->getTidyText(),
            'SideBySide',
            $options,
            ['showHeader' => false]
        );
    }

    private function getTidyText()
    {
        $allowedTags = ['a', 'li', 'img'];
        $result = preg_replace("/<li>/", "<br>– ", $this->text);
        $result = preg_replace("/<\/li>/", "", $result);
        $tidy = new \tidy;
        $tidy->parseString($result, [
            'indent' => true,
            'output-xhtml' => false,
            'show-body-only' => true,
            'wrap' => 1000,
        ], 'utf8');
        $tidy->cleanRepair();
        $result = strip_tags((string) $tidy, $allowedTags);
        $result = preg_replace("/[\r\n]+/", "\n", $result);
        $result = preg_replace("/&gt;/", ">", $result);
        $result = preg_replace("/&lt;/", "<", $result);
        return $result;
    }
}
