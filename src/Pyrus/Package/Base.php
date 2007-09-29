<?php
abstract class PEAR2_Pyrus_Package_Base implements PEAR2_Pyrus_IPackage
{
    protected $packagefile;
    /**
     * The original source of this package
     *
     * This is a chain documenting the steps it took to get this
     * package instantiated, for instance Tar->Abstract
     * @var PEAR2_Pyrus_IPackage
     */
    protected $from;

    function __construct(PEAR2_Pyrus_PackageFile $packagefile)
    {
        $this->packagefile = $packagefile;
    }

    function setFrom(PEAR2_Pyrus_IPackage $from)
    {
        $this->from = $from;
    }

    function offsetExists($offset)
    {
        return $this->packagefile->info->hasFile($offset);
    }

    function offsetGet($offset)
    {
        if (strpos($offset, 'contents://') === 0) {
            return $this->getFileContents(substr($offset, 11));
        }
        return $this->packagefile->info->getFile($offset);
    }

    function offsetSet($offset, $value)
    {
        return;
    }

    function offsetUnset($offset)
    {
        return;
    }

    function current()
    {
        return key($this->packagefile->info->_packageInfo['filelist']);
    }

    function key()
    {
        return 1;
    }

    function next()
    {
        next($this->packagefile->info->_packageInfo['filelist']);
    }

    function rewind()
    {
        reset($this->packagefile->info->_packageInfo['filelist']);
    }

    function __call($func, $args)
    {
        // delegate to the internal object
        return call_user_func_array(array($this->packagefile->info, $func), $args);
    }

    function __get($var)
    {
        return $this->packagefile->info->$var;
    }

    function __toString()
    {
        return $this->packagefile->__toString();
    }

    function valid()
    {
        return key($this->packagefile->info->_packageInfo['filelist']);
    }
}