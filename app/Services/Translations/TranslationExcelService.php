<?php

namespace App\Services\Translations;

use App\Models\Translation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TranslationExcelService
{
    public function exportToXlsx(string $locale): string
    {
        $rows = Translation::query()
            ->where('locale', $locale)
            ->orderBy('key')
            ->get(['key', 'locale', 'value', 'status']);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'key');
        $sheet->setCellValue('B1', 'locale');
        $sheet->setCellValue('C1', 'value');
        $sheet->setCellValue('D1', 'status');

        $i = 2;
        foreach ($rows as $row) {
            $sheet->setCellValue('A' . $i, (string) $row->key);
            $sheet->setCellValue('B' . $i, (string) $row->locale);
            $sheet->setCellValue('C' . $i, (string) ($row->value ?? ''));
            $sheet->setCellValue('D' . $i, (string) ($row->status ?? ''));
            $i++;
        }

        $sheet->freezePane('A2');
        $sheet->getColumnDimension('A')->setWidth(50);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(70);
        $sheet->getColumnDimension('D')->setWidth(14);

        $path = storage_path('app/exports/translations_' . $locale . '_' . now()->format('Ymd_His') . '.xlsx');
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }
}
