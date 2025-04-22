<?php
namespace Services;

use TCPDF;
//composer require tecnickcom/tcpdf
class PdfGeneratorService {
    public function createPDF(string $title, string $content, string $outputPath = 'output.pdf'): void {
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, $title, '', 0, 'L', true, 0, false, false, 0);
        $pdf->Ln();
        $pdf->Write(0, $content);
        $pdf->Output($outputPath, 'F'); // Guardar en disco
    }

    public function streamPDF(string $title, string $content): void {
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, $title, '', 0, 'L', true, 0, false, false, 0);
        $pdf->Ln();
        $pdf->Write(0, $content);
        $pdf->Output('documento.pdf', 'I'); // Mostrar en navegador
    }
}
