<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelExportService
{
    /**
     * Export data to Excel file
     *
     * @param Collection $data
     * @param array $headers
     * @param string $filename
     * @return BinaryFileResponse
     */
    public function export(Collection $data, array $headers, string $filename = 'export'): BinaryFileResponse
    {
        $export = new class($data, $headers) implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize {
            protected $data;
            protected $headers;

            public function __construct($data, $headers)
            {
                $this->data = $data;
                $this->headers = $headers;
            }

            public function collection(): Collection
            {
                return $this->data;
            }

            public function headings(): array
            {
                return $this->headers;
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => [
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'FFFFFF'],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '4472C4'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ],
                ];
            }
        };

        $filename = $this->sanitizeFilename($filename) . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download($export, $filename);
    }

    /**
     * Sanitize filename
     *
     * @param string $filename
     * @return string
     */
    protected function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
    }
}
