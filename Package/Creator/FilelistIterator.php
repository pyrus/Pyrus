<?php
class PEAR2_Pyrus_Package_Creator_FilelistIterator extends ArrayIterator
{
    private $_pkg;
    function __construct($arr, PEAR2_Pyrus_Package $pkg)
    {
        parent::__construct($arr);
        $this->_pkg = $pkg;
    }

    function key()
    {
        $cur = parent::current();
        return $cur['attribs']['name'];
    }

    function current()
    {
        $cur = parent::current();
        $a = 'PEAR2_Pyrus_Installer_Role_' . ucfirst($cur['attribs']['role']);
        $role = new $a(PEAR2_Pyrus_Config::current());
        return $role->getPackagingLocation($this->_pkg, $cur['attribs']);
    }
}