<?php

namespace App\Exports;

use App\Models\TeacherAct;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherActExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    /**
     * @param  Collection<TeacherAct>  $teacherActs
     */
    public function __construct(
        public Collection $teacherActs,
    ) {}

    public function array(): array
    {
        return $this->teacherActs
            ->map(fn (TeacherAct $teacherAct) => [
                $teacherAct->teacher->formatName('full'),
                $teacherAct->date->format('Y-m-d'),
                $teacherAct->date_from->format('Y-m-d'),
                $teacherAct->date_to->format('Y-m-d'),
                $teacherAct->total['groups'],
                $teacherAct->total['lessons'],
                $teacherAct->total['price'],
                $teacherAct->total['price'] * 0.15,
                $teacherAct->year,
            ])
            ->values()
            ->all();
    }

    public function headings(): array
    {
        return [
            'ФИО',
            'дата акта',
            'дата от',
            'дата до',
            'кол-во групп',
            'кол-во занятий',
            'сумма',
            'сумма НДФЛ',
            'год',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
