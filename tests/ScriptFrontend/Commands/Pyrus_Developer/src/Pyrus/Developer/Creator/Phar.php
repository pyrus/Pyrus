<?php
namespace pear2\Pyrus\Developer\Creator;
class Phar implements \pear2\Pyrus\Package\CreatorInterface
{
    /**
     * @var Phar
     */
    protected $phar;
    protected $others;
    protected $path;
    protected $stub;
    protected $format;
    protected $compression;
    private $_classname = 'Phar';
    private $_started = false;

    /**
     * Archive creator for phar, tar, tgz and zip archives.
     *
     * @param string path to primary archive
     * @param string|false stub or false to use default stub of phar archives
     * @param int one of Phar::TAR, Phar::PHAR, or Phar::ZIP
     * @param int if the archive can be compressed (phar and tar), one of Phar::GZ, Phar::BZ2 or Phar::NONE
     *            for no compression
     * @param array an array of arrays containing information on additional archives to create.  The indices are:
     *
     *               0. extension (tar/tgz/zip)
     *               1. format (Phar::TAR, Phar::ZIP, Phar::PHAR)
     *               2. compression (Phar::GZ, Phar::BZ2, Phar::NONE)
     */
    function __construct($path, $stub = false, $fileformat = Phar::TAR, $compression = Phar::GZ, array $others = null)
    {
        if (!class_exists('Phar')) {
            throw new \pear2\Pyrus\Developer\Creator\Exception(
                'Phar extension is not available');
        }
        if (!Phar::canWrite() || !Phar::isValidPharFilename($path, true)) {
            $this->_classname = 'PharData';
        }
        $this->path = $path;
        $this->compression = $compression;
        $this->format = $fileformat;
        $this->others = $others;
        $this->stub = $stub;
    }

    /**
     * save a file inside this package
     * @param string relative path within the package
     * @param string|resource file contents or open file handle
     */
    function addFile($path, $fileOrStream)
    {
        if (!$this->_started) {
            // save package.xml name
            $this->phar->setMetadata($path);
            $this->_started = true;
        }
        $this->phar[$path] = $fileOrStream;
    }

    function addDir($path)
    {
        $this->phar->buildFromDirectory($path);
    }

    /**
     * Initialize the package creator
     */
    function init()
    {
        try {
            if (file_exists($this->path)) {
                @unlink($this->path);
            }
            $ext = strstr(strrchr($this->path, '-'), '.');
            if (!$ext) {
                $ext = strstr(strrchr($this->path, '/'), '.');
                if (!$ext) {
                    $ext = strstr(strrchr($this->path, '\\'), '.');
                }
            }
            if (!$ext) {
                $ext = strstr($this->path, '.');
            }
            $a = $this->_classname;
            $this->phar = new $a($this->path);
            if ($this->phar instanceof Phar) {
                $this->phar = $this->phar->convertToExecutable($this->format,
                                                               $this->compression, $ext);
            } else {
                $this->phar = $this->phar->convertToData($this->format,
                                                         $this->compression, $ext);
            }
            $this->phar->startBuffering();
            if ($this->phar instanceof Phar) {
                $this->phar->setStub($this->stub);
            }
            if ($this->format == Phar::ZIP) {
                $this->compression = $comp;
            }
        } catch (Exception $e) {
            throw new \pear2\Pyrus\Developer\Creator\Exception(
                'Cannot open Phar archive ' . $this->path, $e
            );
        }
        $this->_started = false;
    }

    /**
     * Create an internal directory, creating parent directories as needed
     *
     * @param string $dir
     */
    function mkdir($dir)
    {
        $this->phar->addEmptyDir($dir);
    }

    /**
     * Finish saving the package
     */
    function close()
    {
        if ($this->phar->isFileFormat(Phar::ZIP) && $this->compression !== Phar::NONE) {
            $this->phar->compressFiles($this->compression);
        }
        $this->phar->stopBuffering();
        $newphar = $this->phar;
        $ext = str_replace(array('.tar', '.zip', '.tgz', '.phar'), array('', '', '', ''), basename($this->path)) . '.';
        $ext = substr($ext, strpos($ext, '.'));
        if (count($this->others)) {
            foreach ($this->others as $pathinfo) {
                // remove the old file
                $newpath = str_replace(array('.tar', '.zip', '.tgz', '.phar'), array('', '', '', ''), $this->path);
                $newpath .= '.' .$pathinfo[0];
                if (file_exists($newpath)) {
                    unlink($newpath);
                }
                $extension = $ext . $pathinfo[0];
                $fileformat = $pathinfo[1];
                $compression = $pathinfo[2];

                if ($fileformat != Phar::PHAR) {
                    $newphar = $newphar->convertToData($fileformat, $compression, $extension);
                } else {
                    $newphar = $newphar->convertToExecutable($fileformat, $compression, $extension);
                }
            }
        }
    }
}
