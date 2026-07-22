<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportExport implements FromArray, WithStyles
{
    protected $data;
    protected $title;

    public function __construct(array $data, string $title = 'Report')
    {
        $this->data = $data;
        $this->title = $title;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function styles(Worksheet $sheet)
    {
        // Set title style (first row)
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Set header style (third row)
        if (count($this->data) > 2) {
            $headerRow = 3;
            $lastColumn = $this->getLastColumn();
            
            $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF70AD47'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ]);
        }

        // Set data rows style
        if (count($this->data) > 3) {
            $dataStartRow = 4;
            $dataEndRow = count($this->data);
            $lastColumn = $this->getLastColumn();
            
            $sheet->getStyle("A{$dataStartRow}:{$lastColumn}{$dataEndRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Alternate row colors
            for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                if (($row - $dataStartRow) % 2 == 1) {
                    $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF2F2F2'],
                        ],
                    ]);
                }
            }
        }

        // Auto-size columns
        foreach (range('A', $this->getLastColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set row height for title
        $sheet->getRowDimension(1)->setRowHeight(30);

        return [];
    }

    private function getLastColumn(): string
    {
        if (empty($this->data) || count($this->data) < 3) {
            return 'A';
        }
        
        $maxColumns = max(array_map('count', $this->data));
        return chr(64 + $maxColumns); // Convert number to letter (A, B, C, etc.)
    }
}
