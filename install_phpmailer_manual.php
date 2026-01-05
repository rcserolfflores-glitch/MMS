<?php
// Script to download PHPMailer release and extract into vendor/phpmailer/phpmailer
// Run from project root: php install_phpmailer_manual.php

$zipUrl = 'https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip';
$targetDir = __DIR__ . '/vendor/phpmailer/phpmailer';
$tmpZip = sys_get_temp_dir() . '/phpmailer_master.zip';

echo "Downloading PHPMailer...\n";
$data = @file_get_contents($zipUrl);
if ($data === false) {
    echo "Failed to download $zipUrl. Please ensure internet access.\n";
    exit(1);
}
file_put_contents($tmpZip, $data);

if (!class_exists('ZipArchive')) {
    echo "ZipArchive extension is required. Enable it in php.ini.\n";
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($tmpZip) !== true) {
    echo "Failed to open downloaded zip.\n";
    exit(1);
}

// Ensure target dir
if (!is_dir(dirname($targetDir))) {
    @mkdir(dirname($targetDir), 0755, true);
}

// Extract only src/ files into vendor/phpmailer/phpmailer/src
$extractRoot = $targetDir . '/src';
if (!is_dir($extractRoot)) @mkdir($extractRoot, 0755, true);

for ($i = 0; $i < $zip->numFiles; $i++) {
    $entry = $zip->getNameIndex($i);
    // entries inside PHPMailer-master/src/...
    if (preg_match('#^[^/]+/src/(.+)$#', $entry, $m)) {
        $rel = $m[1];
        $outPath = $extractRoot . '/' . $rel;
        if (substr($entry, -1) === '/') {
            if (!is_dir($outPath)) @mkdir($outPath, 0755, true);
        } else {
            $contents = $zip->getFromIndex($i);
            if ($contents !== false) {
                $dir = dirname($outPath);
                if (!is_dir($dir)) @mkdir($dir, 0755, true);
                file_put_contents($outPath, $contents);
            }
        }
    }
}

$zip->close();
@unlink($tmpZip);

// Create a simple autoload file for the extracted src files
$autoload = $targetDir . '/autoload.php';
$autoloadCode = "<?php\n// Minimal autoload for PHPMailer src files\nrequire_once __DIR__ . '/src/Exception.php';\nrequire_once __DIR__ . '/src/PHPMailer.php';\nrequire_once __DIR__ . '/src/SMTP.php';\n";
file_put_contents($autoload, $autoloadCode);

echo "PHPMailer files extracted to $targetDir\n";
echo "To use: require 'vendor/phpmailer/phpmailer/autoload.php' in your scripts or run Composer instead.\n";
