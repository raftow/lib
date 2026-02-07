<?php
$file_dir_name = dirname(__FILE__);

require_once __DIR__ . '/../../pdf-generator/vendor/autoload.php';

if (!isset($_POST['table_html'])) {
    die('No table data received');
}

$tableHTML = $_POST['table_html'];
$title = $_POST['title'];
$classe_pdf = $_POST['classe_pdf'];
// Build complete HTML for PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        h2 {
            margin-top:20px;
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body dir="rtl">
    <h2>' . $title . '</h2>
    
    ' . $tableHTML . '
</body>
</html>';
//<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
// Initialize mPDF
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'L',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 10,
    'margin_bottom' => 10,
    'direction' => 'rtl'
]);

// Optional: Add header and footer
$mpdf->SetHeader(date('Y-m-d H:i:s'));
$mpdf->SetFooter('Page {PAGENO} of {nbpg}');

// Write HTML to PDF
$mpdf->WriteHTML($html);

// Output PDF
$mpdf->Output($classe_pdf . '_' . date('Y-m-d') . '.pdf', 'D');
