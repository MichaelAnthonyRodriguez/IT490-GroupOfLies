<?php
$sourceDir = '/var/www/sample';
$outputDir = __DIR__ . '/bundles';
$version = '1.0.0';
$zipFile = $outputDir . "/bundle_$version.zip";

// Create output directory if it doesn't exist
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

$zip = new ZipArchive();

if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($sourceDir) + 1);

            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();
    echo "Bundle created: $zipFile\n";
} else {
    echo "Failed to create bundle.\n";
}
?>

