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

    public function scopeLastVersions($query)
    {
        $sub = self::selectRaw(<<<SQL
            entry_id,
            MAX(id) as max_id
        SQL)
            ->groupBy('entry_id');
        $query->joinSub(
            $sub,
            'last_versions',
            fn ($join) => $join
                ->on('instructions.entry_id', '=', 'last_versions.entry_id')
                ->on('instructions.id', '=', 'last_versions.max_id')
        );
    }

    public function getDiffAttribute()
    {
        $prev = $this->versions()
            ->where('created_at', '<', $this->created_at)
            ->latest()
            ->first();

        if ($prev === null) {
            return null;
        }

        return [
            'current' => extract_fields($this, ['created_at', 'title']),
            'prev' => extract_fields($prev, ['created_at', 'title']),
            'diff' => $this->getDiff($prev),
            'diff_all' => $this->getDiff($prev, [
                'context' => Differ::CONTEXT_ALL
            ])
        ];
    }

    private function getDiff(Instruction $prev, $options = [])
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
