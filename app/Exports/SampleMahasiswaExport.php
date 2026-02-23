<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class SampleMahasiswaExport extends DefaultValueBinder implements FromArray, WithHeadings, WithColumnFormatting, ShouldAutoSize, WithCustomValueBinder
{
    private array $rows;
    private array $headings;

    public function __construct(array $rows, array $headings)
    {
        $this->rows = $rows;
        $this->headings = $headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function bindValue(Cell $cell, $value)
    {
        // Jika nilai adalah numerik dan panjangnya lebih dari 10 digit (seperti NIP, NPM, HP)
        // Paksa simpan sebagai teks agar tidak dibulatkan atau menjadi E+
        if (is_numeric($value) && strlen((string)$value) > 10) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT, // NPM
            'D' => NumberFormat::FORMAT_TEXT, // HP
            'E' => NumberFormat::FORMAT_TEXT, // WA
            'F' => NumberFormat::FORMAT_TEXT, // NIP
        ];
    }
}