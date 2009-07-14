<?php
// set $dir and include
if (!($dir = realpath($dir))) return;
chmod($dir, 0777);
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),
                                       RecursiveIteratorIterator::SELF_FIRST)
         as $file) {
    // ensure we can actually delete things
    if (is_dir($file->getPathname())) {
        chmod($file->getPathname(), 0777);
    } else {
        chmod($file->getPathname(), 0666);
    }
}
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
        chmod($file->getPathname(), 0777);
        rmdir($file->getPathname());
    } else {
        chmod($file->getPathname(), 0666);
        unlink($file->getPathname());
    }
}
if (!isset($preservedir)) {
    rmdir($dir);
}
?>
