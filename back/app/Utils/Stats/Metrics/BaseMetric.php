<?php

namespace App\Utils\Stats\Metrics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BaseMetric extends Controller implements MetricInterface
{
    public function __construct(
        public int $id,
        public array $filterValues,
        public string $dateFrom,
        public string $dateTo,
        public string $sqlFormat,
        public string $mode,
    ) {}

    public function getTotalValue(): int
    {
        $request = $this->getRequest();
        $dateField = $this->getDateField();
        $query = $this->getBaseQuery();

        $query->whereRaw("DATE($dateField) BETWEEN ? AND ?", [
            $this->dateFrom,
            $this->dateTo,
        ]);

        $this->filter($request, $query);

        return $this->aggregate($query);
    }

    protected function getRequest(): Request
    {
        return new Request($this->filterValues);
    }

    public function getValueForDate(string $date): int
    {
        $request = $this->getRequest();
        $dateField = $this->getDateField();
        $query = $this->getBaseQuery();

        $query->whereRaw("DATE($dateField) BETWEEN ? AND ?", [
            $this->dateFrom,
            $this->dateTo,
        ]);

        //        if ($mode === 'year') {
        //            [$y, $m, $d] = explode('-', $date);
        //            $query->whereHas('contract', fn($q) => $q->where('year', $y));
        //            $query->whereRaw("`year` = DATE_FORMAT(?, ?)", [
        //                $sqlFormat,
        //                $date,
        //                $sqlFormat,
        //            ]);
        if ($this->mode === 'week') {
            $query->whereRaw("DATE_FORMAT($dateField, ?) = DATE_FORMAT(?, ?)", [
                $this->sqlFormat,
                $date,
                $this->sqlFormat,
            ]);
        } else {
            $query->whereRaw("DATE_FORMAT($dateField, ?) = ?", [
                $this->sqlFormat,
                $date,
            ]);
        }

        $query->whereRaw("DATE($dateField) >= ?", [
            $this->dateFrom,
        ]);

        $this->filter($request, $query);

        return $this->aggregate($query);
    }
}
