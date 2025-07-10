<?php
// Simple test for PDF generation
require_once __DIR__ . '/vendor/autoload.php';

try {
    // Create a simple PDF
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML('<h1>Test PDF</h1><p>This is a test PDF generation.</p>');
    $mpdf->Output('test.pdf', 'D');
} catch (Exception $e) {
    echo "PDF generation failed: " . $e->getMessage();
}
?> 