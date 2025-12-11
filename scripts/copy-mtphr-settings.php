<?php
$source = __DIR__ . '/../vendor/metaphorcreations/mtphr-settings';
$dest   = __DIR__ . '/../includes/mtphr-settings';

if (is_dir($source)) {
    // Delete old directory if it exists
    if (is_dir($dest)) {
        // Recursively delete
        $it = new RecursiveDirectoryIterator($dest, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            $file->isDir() ? rmdir($file) : unlink($file);
        }
        rmdir($dest);
    }

    // Copy recursively
    $it = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);

    mkdir($dest, 0755, true);

    foreach ($files as $file) {
        $target = $dest . DIRECTORY_SEPARATOR . $files->getSubPathName();
        if ($file->isDir()) {
            mkdir($target, 0755, true);
        } else {
            copy($file, $target);
        }
    }

    echo "Copied mtphr-settings to includes/mtphr-settings" . PHP_EOL;
} else {
    echo "mtphr-settings not found in vendor/" . PHP_EOL;
}