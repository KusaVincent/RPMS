<?php
namespace RPMS\APP\Report;

use Dompdf\Dompdf;
use RPMS\APP\Log\LogHandler;

class PDFReport
{
    private static $dompdf;

    public static function generate(string $html, string $paperSize = 'A4', string $orientation = 'portrait'): self
    {
        self::$dompdf = new Dompdf();
        self::$dompdf->loadHtml($html);
        self::$dompdf->setPaper($paperSize, $orientation);
        self::$dompdf->render();

        return new self();
    }

    public function output(string $filename = 'output', bool $preview = true): void
    {
        $filename = $filename;

        if (!self::$dompdf) {
            LogHandler::handle('pdf', 'PDF has not been generated');
            throw new \Exception('PDF has not been generated');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . ($preview ? 'inline' : 'attachment') . '; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Content-Transfer-Encoding: binary');

        self::$dompdf->stream($filename, ['Attachment' => $preview ? 0 : 1]);
    }
}