<?php
abstract class PEAR2_Pyrus_Package_Base implements PEAR2_Pyrus_IPackage
{
    protected $packagefile;

    function __construct(PEAR2_Pyrus_PackageFile $packagefile)
    {
        $this->packagefile = $packagefile;
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