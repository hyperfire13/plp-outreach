<?php

declare(strict_types=1);

use Dompdf\Dompdf;
use Dompdf\Options;

require dirname(__DIR__).'/vendor/autoload.php';

$projectRoot = dirname(__DIR__);
$sourcePath = $projectRoot.'/docs/PLP-Outreach-System-User-Manual.html';
$outputPath = $projectRoot.'/docs/PLP-Outreach-System-User-Manual.pdf';

if (! is_file($sourcePath)) {
    throw new RuntimeException("Manual source not found: {$sourcePath}");
}

$options = new Options;
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);
$options->setChroot($projectRoot);

$dompdf = new Dompdf($options);
$dompdf->loadHtml((string) file_get_contents($sourcePath), 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$canvas = $dompdf->getCanvas();
$font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
$canvas->page_text(
    48,
    810,
    'PLP Outreach User Manual  |  Page {PAGE_NUM} of {PAGE_COUNT}',
    $font,
    7,
    [0.39, 0.45, 0.55]
);

if (file_put_contents($outputPath, $dompdf->output()) === false) {
    throw new RuntimeException("Unable to write PDF: {$outputPath}");
}

echo $outputPath.PHP_EOL;
