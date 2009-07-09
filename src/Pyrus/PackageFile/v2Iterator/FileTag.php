<?php
/**
 * \pear2\Pyrus\PackageFile\v2Iterator\FileTag
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * Store the path to the current file recursively
 *
 * Information can be accessed in three ways:
 *
 * - $file['attribs'] as an array directly
 * - $file->name      as object member, to access attributes
 * - $file->tasks     as pseudo-object, to access each task
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus\PackageFile\v2Iterator;
class FileTag extends \ArrayObject
{
    public $dir;
    /**
     * @var \pear2\Pyrus\PackageFile\v2
     */
    private $_packagefile;
    function __construct($a, $t, $parent)
    {
        $this->_packagefile = $parent;
        parent::__construct($a);
        if ($t === '.') $t = '';
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
            if (!isset($ret['install-as'])) {
                return null;
            }
            return $ret['install-as'];
        }
    }

    function __get($var)
    {
        if ($var == 'packagedname') {
            return $this->dir . $this['attribs']['name'];
        }
        if ($var == 'name') {
            $attribs = parent::offsetGet('attribs');
            if (isset($attribs['install-as'])) {
                return $attribs['install-as'];
            }
            return $this->dir . $this['attribs']['name'];
        }
        if ($var == 'tasks') {
            $ret = $this->getArrayCopy();
            unset($ret['attribs']);
            return $ret;
        }
        if ($var == 'install-as') {
            $attribs = parent::offsetGet('attribs');
            return $attribs['install-as'];
        }
        if (!isset($this['attribs'][$var])) {
            return null;
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
            if ($value instanceof \pear2\Pyrus\Task\Common) {
                $this->_packagefile->setFileAttribute($this->dir .
                    $this['attribs']['name'], $var, $value);
                return;
            }
            throw new \pear2\Pyrus\PackageFile\Exception('Cannot set ' . $var . ' to non-' .
                '\pear2\Pyrus\Task\Common object in file ' . $this->dir .
                $this['attribs']['name']);
        }
        $this->_packagefile->setFileAttribute($this->dir . $this['attribs']['name'],
            $var, $value);
        parent::__construct($this->_packagefile->files[$this->dir . $this['attribs']['name']]);
    }

    function __unset($var)
    {
        if (isset($this['attribs'][$var])) {
            unset($this['attribs'][$var]);
        }
    }
}
