<?php
/**
 * For debugging purposes, see what the package.xml that would be put into the package
 * will be.
 *
 */
class PEAR2_Pyrus_Developer_Creator_Xml implements PEAR2_Pyrus_Package_ICreator
{
    private $_done;
    private $_path;

    function __construct($path)
    {
        if (!($this->_path = @fopen($path, 'w'))) {
            throw new PEAR2_Pyrus_Developer_Creator_Exception('Cannot open path ' .
                $path . ' for writing');
        }
    }

    /**
     * save a file inside this package
     * 
     * This only saves package.xml, which is always the first file sent by the creator.
     * @param string relative path within the package
     * @param string|resource file contents or open file handle
     */
    function addFile($path, $fileOrStream)
    {
        if (!$this->_done) {
            $this->_done = true;
            if (is_resource($fileOrStream)) {
                stream_copy_to_stream($fileOrStream, $this->_path);
            } else {
                fwrite($this->_path, $fileOrStream);
            }
        }
    }

    function addDir($path)
    {
        foreach (new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            $file = (string) $file;
            $relpath = str_replace($path . DIRECTORY_SEPARATOR, '', $file);
            $this->addFile($relpath, $file);
        }
    }

    /**
     * Initialize the package creator
     */
    function init()
    {
        $this->_done = false;
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
        fclose($this->_path);
    }
}