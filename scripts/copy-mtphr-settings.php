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
            
            // Update namespaces in PHP files
            if (pathinfo($target, PATHINFO_EXTENSION) === 'php') {
                $content = file_get_contents($target);
                $modified = false;
                $isIndexFile = basename($target) === 'index.php';
                
                // Replace namespace Mtphr; with namespace Mtphr\PostDuplicator;
                if (preg_match('/^namespace\s+Mtphr\s*;/m', $content)) {
                    $content = preg_replace('/^namespace\s+Mtphr\s*;/m', 'namespace Mtphr\\PostDuplicator;', $content);
                    $modified = true;
                }
                
                // For index.php, add namespace if it doesn't exist (after <?php)
                if ($isIndexFile && !preg_match('/^namespace\s+/m', $content)) {
                    // Insert namespace after <?php and before any use statements
                    $content = preg_replace(
                        '/^(<\?php)(\s*\n)/m',
                        '$1$2namespace Mtphr\\PostDuplicator;$2',
                        $content,
                        1
                    );
                    $modified = true;
                }
                
                // Replace use Mtphr\Settings; with use Mtphr\PostDuplicator\Settings;
                if (preg_match('/^use\s+Mtphr\\\Settings\s*;/m', $content)) {
                    $content = preg_replace('/^use\s+Mtphr\\\Settings\s*;/m', 'use Mtphr\\PostDuplicator\\Settings;', $content);
                    $modified = true;
                }
                
                // For index.php, update function_exists check to use __NAMESPACE__
                if ($isIndexFile && preg_match("/function_exists\s*\(\s*['\"]MTPHR_SETTINGS['\"]\s*\)/", $content)) {
                    $content = preg_replace(
                        "/function_exists\s*\(\s*['\"]MTPHR_SETTINGS['\"]\s*\)/",
                        "function_exists( __NAMESPACE__ . '\\MTPHR_SETTINGS' )",
                        $content
                    );
                    $modified = true;
                }
                
                if ($modified) {
                    file_put_contents($target, $content);
                }
            }
        }
    }

    echo "Copied mtphr-settings to includes/mtphr-settings and updated namespaces" . PHP_EOL;
} else {
    echo "mtphr-settings not found in vendor/" . PHP_EOL;
}