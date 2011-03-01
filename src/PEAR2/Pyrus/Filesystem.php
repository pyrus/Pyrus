<?php
namespace PEAR2\Pyrus;

use FilesystemIterator;

abstract class Filesystem
{
    /**
     * Replace forward and backward slashes with DIRECTORY_SEPARATOR.
     *
     * @static
     * @param string $path A file system path
     * @return string
     */
    public static function path($path)
    {
        return str_replace(array('\\','/'), DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Combine the arguments of the function into a file path.
     *
     * @static
     * @return string
     */
    public static function combine()
    {
        return implode(DIRECTORY_SEPARATOR, func_get_args());
    }

    public static function explode($path)
    {
        return explode(array('\\','/'), $path);
    }

    public static function rmrf($path, $onlyEmptyDirs = false, $strict = true)
    {
        $paths = array();
        $oldPerms = array();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::SELF_FIRST);
        while ($iterator->valid()) {
            if ($iterator->isDot()) {
                $iterator->next();
                continue;
            }
            $pathName = $iterator->current()->getPathName();
            $paths[] = $pathName;
            if ($strict) {
                $oldPerms[$pathName] = fileperms($pathName);
            }

            if (is_dir($pathName)) {
                chmod($pathName, 0777);
            } else {
                chmod($pathName, 0666);
            }

            $iterator->next();
        }

        $paths = array_unique(array_reverse($paths));
        try {
            foreach ($paths as $filePath) {
                if (is_dir($filePath)) {
                    if (rmdir($filePath)) {
                        continue;
                    }
                    if (!$strict) {
                        continue;
                    }

                    throw new IOException('Unable to fully remove ' . $path);
                }

                if ($onlyEmptyDirs) {
                    if (!$strict) {
                        continue;
                    }

                    throw new IOException(
                        'Unable to fully remove ' . $path . ', directory is not empty');
                }

                if (!unlink($filePath)) {
                    throw new IOException(
                        'Unable to fully remove ' . $path);
                }
            }
        } catch (IOException $e) {
            // restore original permissions
            foreach ($oldPerms as $file => $perms) {
                if (file_exists($file)) {
                    chmod($file, $perms);
                }
            }

            throw $e;
        }

        // ensure rmdir works
        chmod($path, 0777);
        if (!rmdir($path) && $strict) {
            throw new IOException('Unable to fully remove ' . $path);
        }
    }

    public static function copyDir($source, $target)
    {
        if (!file_exists($source)) {
            return;
        }

        try {
            $done = array();
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source),
                                                   \RecursiveIteratorIterator::SELF_FIRST);
            while ($iterator->valid()) {
                if ($iterator->isDot()) {
                    $iterator->next();
                    continue;
                }

                $file = $iterator->current();
                $targetPath = $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                $iterator->next();

                // It seems RecursiveIteratorIterator can return the save file twice
                if (isset($done[$targetPath])) {
                    continue;
                }
                $done[$targetPath] = true;

                // Copy directory or file
                if ($file->isDir()) {
                    if (!mkdir($targetPath, $file->getPerms())) {
                        throw new IOException(
                            'Unable to copy directory, failed to create directory ' . $targetPath . ' - ' .  $file->getPathname());
                    }
                } elseif (!copy($file->getPathName(), $targetPath)) {
                    throw new IOException(
                        'Unable to copy directory, failed to copy the file');

                }
                if (!chmod($targetPath, $file->getPerms())) {
                    throw new IOException(
                        'Unable to copy directory, failed to set permissions');
                }

                if (!touch($targetPath, $file->getMTime(), $file->getATime())) {
                    throw new IOException(
                        'Unable to copy directory, touch failed');
                }
            }
        } catch (\UnexpectedValueException $e) {
            throw new IOException('directory copy failed: ' . $e->getMessage(), $e);
        }
    }
}
