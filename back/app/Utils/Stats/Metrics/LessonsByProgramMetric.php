<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Lesson;
use Illuminate\Support\Collection;

class LessonsByProgramMetric extends BaseMetric
{
    protected $filters = [
        'programs' => ['program'],
    ];

    /** Кэш интервалов: [ [program, year, first_date, last_date], ... ] */
    protected ?Collection $intervals = null;

    // ВАЖНО: не даём базовому классу фильтровать по дате этот запрос
    public function getDateField(): string
    {
        // вернуть фиктивное имя или переопределить в BaseMetric логику применения date-фильтра
        return 'date';
    }

    public function aggregate($query): int
    {
        // Не используется для этой метрики. Либо оставь 0, либо брось исключение/верни getValueForDate(currentDate()).
        return 0;
    }

    /** Сколько (program,year) покрывают указанную дату */
    public function getValueForDate(string $date): int
    {
        $cnt = 0;
        foreach ($this->intervals() as $row) {
            // строковые сравнения достаточны для 'YYYY-MM-DD'
            if ($date >= $row['first_date'] && $date <= $row['last_date']) {
                $cnt++;
            }
        }

        return $cnt;
    }

    /**
     * Подтягиваем интервалы один раз и приводим к скалярам (строки Y-m-d).
     *
     * @return Collection<int, array{program:string,year:int,first_date:string,last_date:string}>
     */
    protected function intervals(): Collection
    {
        if (! isset($this->intervals)) {
            $query = $this->getBaseQuery();

            // применяем только НЕ-даты фильтры (program/year)
            $this->filter($this->getRequest(), $query, $this->filters);

            $rows = $query->get();

            // нормализация в простые массивы/строки
            $this->intervals = $rows->map(static function ($r) {
                return [
                    'contract_version_program_id' => $r->contract_version_program_id,
                    'first_date' => (string) $r->first_date, // 'YYYY-MM-DD'
                    'last_date' => (string) $r->last_date,
                ];
            });
        }

        return $this->intervals;
    }

    public function getBaseQuery()
    {
        return Lesson::query()
            ->join('client_lessons', 'lessons.id', '=', 'client_lessons.lesson_id')
            ->selectRaw('contract_version_program_id, MIN(lessons.date) AS first_date, MAX(lessons.date) AS last_date')
            ->groupBy('contract_version_program_id');
    }

    /** Сколько «покрытых дней» суммарно в окне [dateFrom..dateTo] */
    public function getTotalValue(): int
    {
        $dateFrom = substr((string) $this->dateFrom, 0, 10);
        $dateTo = substr((string) $this->dateTo, 0, 10);

        $total = 0;
        foreach ($this->intervals() as $row) {
            // нет пересечения
            if ($row['last_date'] < $dateFrom || $row['first_date'] > $dateTo) {
                continue;
            }
            $s = max($row['first_date'], $dateFrom);
            $e = min($row['last_date'], $dateTo);

            // diffInDays + 1 без Carbon
            $total += (int) ((strtotime($e) - strtotime($s)) / 86400) + 1;
        }

        return $total;
    }

    protected function filterPrograms($query, array $programs)
    {
        if (count($programs) === 0) {
            return;
        }

        $query
            ->join('contract_version_programs as cvp',
                fn ($join) => $join
                    ->on('cvp.id', '=', 'contract_version_program_id')
                    ->whereIn('cvp.program', $programs)
            );
    }
}
