<?php
// set $dir and include
if (!($dir = realpath($dir))) return;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),
                                       RecursiveIteratorIterator::CHILD_FIRST)
         as $file) {
    if (isset($exceptions) && in_array($file->getPathName(), $exceptions, 1)) {
        continue;
    }
    if ($file->getFilename() == '.' || $file->getFilename() == '..') {
        continue;
    }
    if (is_dir($file->getPathname())) {
        rmdir($file->getPathname());
    } else {
        unlink($file->getPathname());
    }
}
if (!isset($preservedir)) {
    rmdir($dir);
}
?>
