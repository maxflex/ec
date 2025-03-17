<?php

namespace App\Utils\Stats\Metrics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BaseMetric extends Controller implements MetricInterface
{
    public function __construct(
        public array $filterValues,
        public string $date,
        public string $dateFrom,
        public string $sqlFormat,
        public string $mode,
    ) {}

    public function getTotals(): int
    {
        $request = new Request($this->filterValues);
        $dateField = $this->getDateField();
        $query = $this->getQuery();

        $query->whereRaw("DATE(`$dateField`) BETWEEN ? AND ?", [
            $this->dateFrom,
            $this->date,
        ]);

        $this->filter($request, $query);

        return static::getQueryValue($query);
    }

    public function getValue(): int
    {
        $request = new Request($this->filterValues);
        $dateField = $this->getDateField();
        $query = $this->getQuery();

        //        if ($mode === 'year') {
        //            [$y, $m, $d] = explode('-', $date);
        //            $query->whereHas('contract', fn($q) => $q->where('year', $y));
        //            $query->whereRaw("`year` = DATE_FORMAT(?, ?)", [
        //                $sqlFormat,
        //                $date,
        //                $sqlFormat,
        //            ]);
        if ($this->mode === 'week') {
            $query->whereRaw("DATE_FORMAT(`$dateField`, ?) = DATE_FORMAT(?, ?)", [
                $this->sqlFormat,
                $this->date,
                $this->sqlFormat,
            ]);
        } else {
            $query->whereRaw("DATE_FORMAT(`$dateField`, ?) = ?", [
                $this->sqlFormat,
                $this->date,
            ]);
        }

        $query->whereRaw("DATE(`$dateField`) >= ?", [
            $this->dateFrom,
        ]);

        $this->filter($request, $query);

        return static::getQueryValue($query);
    }
}
