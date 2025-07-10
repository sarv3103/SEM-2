<?php
// Simple test to check if PDF generation works
require_once __DIR__ . '/../vendor/autoload.php';

// Set headers for PDF download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Simple_Test.pdf"');

try {
    // Create a very simple PDF
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4'
    ]);
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Simple Test PDF</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { background: #0077cc; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>✅ PDF Generation Test</h1>
            <p>If you can see this, PDF generation is working!</p>
        </div>
        <div class="content">
            <h2>Test Information</h2>
            <p><strong>Generated on:</strong> ' . date('Y-m-d H:i:s') . '</p>
            <p><strong>Test ID:</strong> SIMPLE_' . time() . '</p>
            <p><strong>Status:</strong> PDF generation successful</p>
            <hr>
            <p>This is a simple test to verify that:</p>
            <ul>
                <li>mPDF library is working</li>
                <li>PHP can generate PDFs</li>
                <li>File downloads are working</li>
            </ul>
        </div>
    </body>
    </html>';
    
    $mpdf->WriteHTML($html);
    $mpdf->Output('Simple_Test.pdf', 'D');
    
} catch (Exception $e) {
    // If PDF fails, return error as HTML
    header('Content-Type: text/html');
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>PDF Test Error</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 50px; text-align: center; }
            .error { color: #721c24; background: #f8d7da; padding: 20px; border-radius: 10px; }
        </style>
    </head>
    <body>
        <div class="error">
            <h1>❌ PDF Generation Failed</h1>
            <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
            <p>This indicates a problem with:</p>
            <ul style="text-align: left; display: inline-block;">
                <li>mPDF library installation</li>
                <li>PHP configuration</li>
                <li>File permissions</li>
            </ul>
        </div>
    </body>
    </html>';
}
?> 