<?php
/**
 * utils/PDFService.php
 * Ce service centralise la génération de PDF. 
 * Actuellement il prépare le HTML pour window.print() par défaut, 
 * mais est prêt à utiliser Dompdf dès son installation via composer.
 */

class PDFService {
    
    public static function stream($html, $filename) {
        // Détecter si Dompdf est installé
        $dompdfPath = __DIR__ . '/../vendor/autoload.php';
        
        if (file_exists($dompdfPath)) {
            require_once $dompdfPath;
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->loadHtml($html);
            $dompdf->render();
            $dompdf->stream($filename, ["Attachment" => false]);
            exit;
        } else {
            // Fallback : On injecte un script d'auto-impression dans le HTML
            // Cela garantit au moins que l'utilisateur voit la même vue 'imprimable'
            echo $html;
            echo "<script>window.onload = function() { window.print(); }</script>";
            exit;
        }
    }
}
