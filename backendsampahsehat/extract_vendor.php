<?php
$zipFile = __DIR__ . '/vendor.zip';
$extractTo = __DIR__;

$zip = new ZipArchive();
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    echo "Extracted vendor.zip successfully!\n";
} else {
    echo "Failed to open vendor.zip\n";
}
