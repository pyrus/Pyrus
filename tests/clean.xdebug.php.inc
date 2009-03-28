<?php
// clean up stray .xdebug files
$dir = __DIR__;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST) as $name => $file) {
    if ($file->isDir()) {
        continue;
    }
    if (strpos($file->getFilename(), '.xdebug')) {
        unlink($file->getPathname());
    }
}
?>
