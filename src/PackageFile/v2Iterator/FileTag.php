<?php
/**
 * Store the path to the current file recursively
 * 
 * Information can be accessed in three ways:
 * 
 * - $file['attribs'] as an array directly
 * - $file->name      as object member, to access attributes
 * - $file->tasks     as pseudo-object, to access each task
 */
class PEAR2_Pyrus_PackageFile_v2Iterator_FileTag extends ArrayObject
{
    public $dir;
    /**
     * @var PEAR2_Pyrus_PackageFile_v2
     */
    private $_packagefile;
    function __construct($a, $t, $parent)
    {
        $this->_packagefile = $parent;
        parent::__construct($a);
        $this->dir = $t;
        if ($this->dir && $this->dir != '/') $this->dir .= '/';
    }

    /**
     * Hide the install-as attribute (it is merged into the "name" attribute)
     *
     * @param string $offset
     * @return mixed
     */
    function offsetGet($offset)
    {
        if ($offset == 'attribs') {
            $ret = parent::offsetGet('attribs');
            if (isset($ret['install-as'])) {
                unset($ret['install-as']);
            }
            return $ret;
        }
        if ($offset == 'install-as') {
            $ret = parent::offsetGet('attribs');
            return $ret['install-as'];
        }
    }

    function __get($var)
    {
        if ($var == 'name') {
            if (isset($this['install-as'])) {
                return $this['install-as'];
            }
            return $this->dir . $this['attribs']['name'];
        }
        if ($var == 'tasks') {
            $ret = $this->getArrayCopy();
            unset($ret['attribs']);
            return $ret;
        }
        return $this['attribs'][$var];
    }

    /**
     * Allow setting of attributes and tasks directly
     *
     * @param string $var
     * @param string|object $value
     */
    function __set($var, $value)
    {
        if (strpos($var, $this->_packagefile->getTasksNs()) === 0) {
            // setting a file task
            if ($value instanceof PEAR2_Pyrus_Task_Common) {
                $this->_packagefile->setFileAttribute($this->_dir .
                    $this['attribs']['name'], $var, $value->getArrayCopy());
                return;
            }
            throw new PEAR2_Pyrus_PackageFile_Exception('Cannot set ' . $var . ' to non-' .
                'PEAR2_Pyrus_Task_Common object in file ' . $this->dir .
                $this['attribs']['name']);
        }
        $this->_packagefile->setFileAttribute($this->dir . $this['attribs']['name'],
            $var, $value);
    }
}
