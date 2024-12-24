<?php
require_once(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php');
require_once(__DIR__ . '/../vendor/tecnickcom/tcpdf/include/tcpdf_fonts.php');

// Font file path (you need to download the TTF file first)
$fontfile = __DIR__ . '/Battambang-Regular.ttf';
$fontname = 'khmerfont'; // font name to be used in TCPDF

// Create TCPDF Fonts object
$fontTools = new TCPDF_FONTS();

// Set the destination directory to TCPDF fonts folder
$fontDestDir = __DIR__ . '/../vendor/tecnickcom/tcpdf/fonts';

// Convert font for TCPDF with explicit destination directory
$fontTools->addTTFfont($fontfile, 'TrueTypeUnicode', '', 96, $fontDestDir);

echo "Font converted successfully!\n";
echo "Font files were placed in: " . $fontDestDir . "\n";
?>
