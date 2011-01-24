<?php
namespace PEAR2\Pyrus\Developer\Creator;
class Zip implements \PEAR2\Pyrus\Package\CreatorInterface
{
    /**
     * Path to archive file
     *
     * @var string
     */
    protected $archive;
    /**
     * @var ZIPArchive
     */
    protected $zip;
    protected $path;
    function __construct($path)
    {
        if (!class_exists('ZIPArchive')) {
            throw new \PEAR2\Pyrus\Developer\Creator\Exception(
                'Zip extension is not available');
        }
        $this->path = $path;
    }

    /**
     * save a file inside this package
     * @param string relative path within the package
     * @param string|resource file contents or open file handle
     */
    function addFile($path, $fileOrStream)
    {
        if (is_resource($fileOrStream)) {
            $this->zip->addFromString($path, stream_get_contents($fileOrStream));
        } else {
            $this->zip->addFromString($path, $fileOrStream);
        }
    }

    function addDir($path)
    {
        foreach (new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            $contents = file_get_contents((string)$file);
            $relpath = str_replace($path . DIRECTORY_SEPARATOR, '', $file);
            $this->addFile($relpath, $contents);
        }
    }

    /**
     * Initialize the package creator
     */
    function init()
    {
        $this->zip = new ZipArchive;
        if (true !== $this->zip->open($this->path, ZIPARCHIVE::CREATE)) {
            throw new \PEAR2\Pyrus\Developer\Creator\Exception(
                'Cannot open ZIP archive ' . $this->path
            );
        }
    }

    /**
     * Create an internal directory, creating parent directories as needed
     * 
     * This is a no-op for the tar creator
     * @param string $dir
     */
    function mkdir($dir)
    {
    }

    /**
     * Finish saving the package
     */
    function close()
    {
        $this->zip->close();
    }
}