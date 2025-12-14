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

    mkdir($dest, 0755, true);

    // Copy index.php
    $indexSource = $source . DIRECTORY_SEPARATOR . 'index.php';
    if (file_exists($indexSource)) {
        $indexDest = $dest . DIRECTORY_SEPARATOR . 'index.php';
        copy($indexSource, $indexDest);
        
        // Update namespaces in index.php
        $content = file_get_contents($indexDest);
        $modified = false;
        
        // Replace namespace Mtphr; with namespace Mtphr\PostDuplicator;
        if (preg_match('/^namespace\s+Mtphr\s*;/m', $content)) {
            $content = preg_replace('/^namespace\s+Mtphr\s*;/m', 'namespace Mtphr\\PostDuplicator;', $content);
            $modified = true;
        }
        
        if ($modified) {
            file_put_contents($indexDest, $content);
        }
    }

    // Copy assets directory recursively
    $assetsSource = $source . DIRECTORY_SEPARATOR . 'assets';
    if (is_dir($assetsSource)) {
        $assetsDest = $dest . DIRECTORY_SEPARATOR . 'assets';
        $it = new RecursiveDirectoryIterator($assetsSource, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {
            $target = $assetsDest . DIRECTORY_SEPARATOR . $files->getSubPathName();
            if ($file->isDir()) {
                mkdir($target, 0755, true);
            } else {
                copy($file, $target);
                
                // Update namespaces in PHP files
                if (pathinfo($target, PATHINFO_EXTENSION) === 'php') {
                    $content = file_get_contents($target);
                    $modified = false;
                    
                    // Replace namespace Mtphr; with namespace Mtphr\PostDuplicator;
                    if (preg_match('/^namespace\s+Mtphr\s*;/m', $content)) {
                        $content = preg_replace('/^namespace\s+Mtphr\s*;/m', 'namespace Mtphr\\PostDuplicator;', $content);
                        $modified = true;
                    }
                    
                    if ($modified) {
                        file_put_contents($target, $content);
                    }
                }
            }
        }
    }

    echo "Copied mtphr-settings to includes/mtphr-settings and updated namespaces" . PHP_EOL;
} else {
    echo "mtphr-settings not found in vendor/" . PHP_EOL;
}