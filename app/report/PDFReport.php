<?php
namespace RPMS\App\Report;

use Dompdf\Dompdf;
use RPMS\App\Log\LogHandler;
use RPMS\App\Security\Header\HeaderSetting;

class PDFReport
{
    private static object $dompdf;

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

        $contentType = 'application/pdf';
        $contentDisposition = ($preview ? 'inline' : 'attachment');
        
        HeaderSetting::setHeader('Content-Type', $contentType);
        HeaderSetting::setHeader('Content-Disposition', $contentDisposition . '; filename="' . $filename . '"');
        HeaderSetting::setHeader('Cache-Control', 'private, max-age=0, must-revalidate');
        HeaderSetting::setHeader('Pragma', 'public');
        HeaderSetting::setHeader('Content-Transfer-Encoding', 'binary');

        self::$dompdf->stream($filename, ['Attachment' => $preview ? 0 : 1]);
    }
}