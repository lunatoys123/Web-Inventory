<?php
require_once __DIR__ . '/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf();
$mpdf->autoLangToFont = true;
$mpdf->autoScriptToLang = true;
$mpdf->WriteHTML('<h1>我是劉建東</h1>');
$mpdf->Output();