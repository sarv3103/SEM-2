<?php
require_once __DIR__ . '/../vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML('<h1>Hello, mPDF is working!</h1>');
$mpdf->Output(); 