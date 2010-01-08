<?php
namespace pear2\Pyrus\Developer\Creator;
class Tar implements \pear2\Pyrus\Package\CreatorInterface
{
    /**
     * Path to archive file
     *
     * @var string
     */
    protected $archive;
    /**
     * Temporary stream used for creating the archive
     *
     * @var stream
     */
    protected $tmp;
    protected $path;
    protected $compress;
    function __construct($path, $compress = 'zlib')
    {
        $this->compress = $compress;
        if ($compress === 'bz2' && !function_exists('bzopen')) {
            throw new \pear2\Pyrus\Developer\Creator\Exception(
                'bzip2 extension not available');
        }
        if ($compress === 'zlib' && !function_exists('gzopen')) {
            throw new \pear2\Pyrus\Developer\Creator\Exception(
                'zlib extension not available');
        }
        $this->path = $path;
    }

    /**
     * save a file inside this package
     * 
     * This code is modified from Vincent Lascaux's File_Archive
     * package, which is licensed under the LGPL license.
     * @param string relative path within the package
     * @param string|resource file contents or open file handle
     */
    function addFile($path, $fileOrStream)
    {
        clearstatcache();
        if (is_resource($fileOrStream)) {
            $stat = fstat($fileOrStream);
        } else {
            $stat = array(
                'mode' => 0x8000 + 0644,
                'uid' => 0,
                'gid' => 0,
                'size' => strlen($fileOrStream),
                'mtime' => time(),
            );
        }

        $link = null;
        if ($stat['mode'] & 0x4000) {
            $type = 5;        // Directory
        } else if ($stat['mode'] & 0x8000) {
            $type = 0;        // Regular
        } else if ($stat['mode'] & 0xA000) {
            $type = 1;        // Link
            $link = @readlink($current);
        } else {
            $type = 9;        // Unknown
        }

        $filePrefix = '';
        if (strlen($path) > 255) {
            throw new \pear2\Pyrus\Developer\Creator\Exception(
                "$path is too long, must be 255 characters or less"
            );
        } else if (strlen($path) > 100) {
            $filePrefix = substr($path, 0, strlen($path)-100);
            $path = substr($path, -100);
        }

        $block = pack('a100a8a8a8a12A12',
                $path,
                decoct($stat['mode']),
                sprintf('%6s ',decoct($stat['uid'])),
                sprintf('%6s ',decoct($stat['gid'])),
                sprintf('%11s ',decoct($stat['size'])),
                sprintf('%11s ',decoct($stat['mtime']))
            );

        $blockend = pack('a1a100a6a2a32a32a8a8a155a12',
            $type,
            $link,
            'ustar',
            '00',
            'Pyrus',
            'Pyrus',
            '',
            '',
            $filePrefix,
            '');

        $checkheader = array_merge(str_split($block), str_split($blockend));
        if (!function_exists('_pear2tarchecksum')) {
            function _pear2tarchecksum($a, $b) {return $a + ord($b);}
        }
        $checksum = 256; // 8 * ord(' ');
        $checksum += array_reduce($checkheader, '_pear2tarchecksum');

        $checksum = pack('a8', sprintf('%6s ', decoct($checksum)));

        fwrite($this->tmp, $block . $checksum . $blockend, 512);
        if (is_resource($fileOrStream)) {
            stream_copy_to_stream($fileOrStream, $this->tmp);
            if ($stat['size'] % 512) {
                fwrite($this->tmp, str_repeat("\0", 512 - $stat['size'] % 512));
            }
        } else {
            fwrite($this->tmp, $fileOrStream);
            if (strlen($fileOrStream) % 512) {
                fwrite($this->tmp, str_repeat("\0", 512 - strlen($fileOrStream) % 512));
            }
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
        switch ($this->compress) {
            case 'zlib' :
                $this->tmp = gzopen($this->path, 'wb');
                break;
            case 'bz2' :
                $this->tmp = bzopen($this->path, 'wb');
                break;
            case 'none' :
                $this->tmp = fopen($this->path, 'wb');
                break;
            default :
                throw new \pear2\Pyrus\Developer\Creator\Exception(
                    'unknown compression type ' . $this->compress);
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
        fwrite($this->tmp, pack('a1024', ''));
        fclose($this->tmp);
    }
}