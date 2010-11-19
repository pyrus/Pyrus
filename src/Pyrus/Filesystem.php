<?php
namespace PEAR2\Pyrus;

abstract class Filesystem {
    /**
     * Replace forward and backward slashes with DIRECTORY_SEPARATOR.
     *
     * @static
     * @param string $path A file system path
     * @return string
     */
    public static function path($path) {
        return str_replace(array('\\','/'), DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Combine the arguments of the function into a file path.
     *
     * @static
     * @return string
     */
    public static function combine() {
        return implode(DIRECTORY_SEPARATOR, func_get_args());
    }

    public static function explode($path) {
        return explode(array('\\','/'), $path);
    }

    public static function rmrf($path, $onlyEmptyDirs = false, $strict = true)
    {
        $oldPerms = array();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path),
                                               \RecursiveIteratorIterator::SELF_FIRST);
        while ($iterator->valid()) {
            if ($iterator->isDot()) {
                $iterator->next();
                continue;
            }
            $pathName = $iterator->current()->getPathName();

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

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path),
                                               \RecursiveIteratorIterator::CHILD_FIRST);
        try {
            while ($iterator->valid()) {
                if ($iterator->isDot()) {
                    $iterator->next();
                    continue;
                }
                $file = $iterator->current();
                $iterator->next();

                if ($file->isDir()) {
                    if (@rmdir($file->getPathname())) {
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

                if (!@unlink($file->getPathname())) {
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
        if (!@rmdir($path) && $strict) {
            throw new IOException('Unable to fully remove ' . $path);
        }
    }

    public static function copyDir($source, $target)
    {
        if (!file_exists($source)) {
            return;
        }

        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source),
                                                   \RecursiveIteratorIterator::SELF_FIRST);
            while ($iterator->valid()) {
                if ($iterator->isDot()) {
                    $iterator->next();
                    continue;
                }

                $file = $iterator->current();
                $targetPath = $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

                if ($file->isDir()) {
                    if (!@mkdir($targetPath, $file->getPerms())) {
                        throw new IOException(
                            'Unable to copy directory, failed to create directory');
                    }
                } elseif (!@copy($file->getPathName(), $targetPath)) {
                    throw new IOException(
                        'Unable to copy directory, failed to copy the file');

                }
                if (!@chmod($targetPath, $file->getPerms())) {
                    throw new IOException(
                        'Unable to copy directory, failed to set permissions');
                }

                if (!@touch($targetPath, $file->getMTime(), $file->getATime())) {
                    throw new IOException(
                        'Unable to copy directory, touch failed');
                }
                $iterator->next();
            }
        } catch (\UnexpectedValueException $e) {
            throw new IOException('directory copy failed: ' . $e->getMessage(), $e);
        }
    }
}
