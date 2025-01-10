<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class StatsExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(
        public array $data,
        public array $metrics
    )
    {
    }

    public function array(): array
    {
        return array_map(fn($item) => array_merge(
            [$item['date']],
            $item['values']
        ), $this->data);
    }

    public function headings(): array
    {
        return array_merge(
            ['дата'],
            collect($this->metrics)->map(fn($e) => str($e['label'])->lower())->all()
        );
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
