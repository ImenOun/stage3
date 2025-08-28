<?php
namespace App\Service;

use PhpOffice\PhpWord\TemplateProcessor;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    public function generateFromTemplate(string $templatePath, array $data, string $outputFileName): void
    {
        // 1. Charger le modèle .docx
        $templateProcessor = new TemplateProcessor($templatePath);

        // 2. Remplacer les balises par les vraies données
        foreach ($data as $key => $value) {
            $templateProcessor->setValue($key, $value);
        }

        // 3. Sauvegarder temporairement en .docx
        $tempDocx = sys_get_temp_dir() . '/' . uniqid() . '.docx';
        $templateProcessor->saveAs($tempDocx);

        // 4. Convertir en HTML avec PHPWord
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($tempDocx);
        $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
        $tempHtml = sys_get_temp_dir() . '/' . uniqid() . '.html';
        $htmlWriter->save($tempHtml);

        // 5. Convertir HTML en PDF avec DomPDF
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(file_get_contents($tempHtml));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 6. Envoyer au navigateur
        $dompdf->stream($outputFileName, ["Attachment" => false]);
    }
}
