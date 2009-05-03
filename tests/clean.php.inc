<?php
// set $dir and include
if (!($dir = realpath($dir))) return;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST) as $name => $file) {
    if (isset($exceptions) && in_array($file->getPathName(), $exceptions, 1)) {
        continue;
    }
    if ($file->isDir() && basename($file->getPathName()) != '.' && basename($file->getPathName() != '..')) {
        rmdir($file->getPathName());
        continue;
    }
    unlink($file->getPathname());
}
if (!isset($preservedir)) {
    rmdir($dir);
}
?>
