<?php

namespace RPMS\APP\Report;

use RPMS\APP\Log\LogHandler;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelReport
{
    private $filename;
    private $data;
    private $format;

    public static function generate(string $filename, array $data, string $format = 'xlsx'): self
    {
        $report = new self();
        $report->filename = $filename;
        $report->data = $data;
        $report->format = $format;

        $report->generateFile();

        return $report;
    }

    private function generateFile(): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $activeWorksheet = $spreadsheet->getActiveSheet();

            $this->populateData($activeWorksheet, $this->data);

            $writer = ($this->format === 'csv') ? new Csv($spreadsheet) : new Xlsx($spreadsheet);
            $writer->save($this->filename);
        } catch (\Throwable $e) {
            LogHandler::handle('excel', 'Failed to generate Excel file: ' . $e->getMessage());
            throw new \Exception('Failed to generate Excel file: ' . $e->getMessage());
        }
    }

    public function preview(bool $forceDownload = false): void
    {
        try {
            $fileExtension = pathinfo($this->filename, PATHINFO_EXTENSION);
            $fileMimeTypes = [
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'csv' => 'text/csv',
            ];

            if (array_key_exists($fileExtension, $fileMimeTypes)) {
                $fileMimeType = $fileMimeTypes[$fileExtension];

                header('Content-Type: ' . $fileMimeType);
                if ($forceDownload) {
                    header('Content-Disposition: attachment; filename="' . basename($this->filename) . '"');
                } else {
                    header('Content-Disposition: inline; filename="' . basename($this->filename) . '"');
                }
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . filesize($this->filename));

                readfile($this->filename);
            } else {
                LogHandler::handle('excel', 'Invalid file extension: ' . $fileExtension);
                throw new \Exception('Invalid file extension: ' . $fileExtension);
            }
        } catch (\Throwable $e) {
            LogHandler::handle('excel', 'Failed to preview Excel file: ' . $e->getMessage());
            throw new \Exception('Failed to preview Excel file: ' . $e->getMessage());
        }
    }

    private function populateData(object $worksheet, array $data): void
    {
        $row = 1;
        foreach ($data as $rowData) {
            $column = 1;
            foreach ($rowData as $cellData) {
                $worksheet->setCellValueByColumnAndRow($column, $row, $cellData);
                $column++;
            }
            $row++;
        }
    }
}