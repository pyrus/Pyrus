<?php
// set $dir and include
if (!($dir = realpath($dir))) return;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST) as $name => $file) {
    if ($file->isDir()) {
        rmdir($file->getPathName());
        continue;
    }
    unlink($file->getPathname());
}
rmdir($dir);
?>
