<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpreadsheetExportService
{
    /**
     * Stream a query to XLSX in chunks.
     *
     * @param  array<int, string>  $headings
     * @param  callable(mixed $out, callable $write): void  $writer  Receives a row-writer closure.
     */
    public function stream(string $filename, array $headings, callable $writer): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$headings], null, 'A1');
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;
        $writer(function (array $record) use ($sheet, &$row) {
            $sheet->fromArray([$record], null, 'A'.$row);
            $row++;
        });

        foreach (range(1, count($headings)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
