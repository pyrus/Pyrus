<?php
/**
 * PEAR2_Pyrus_PackageFile_v2Iterator_ScriptFileFilterIterator
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
 * Class which iterates over all files, only returning those that contain script tasks.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_PackageFile_v2Iterator_ScriptFileFilterIterator extends FilterIterator
{
    private $_inner;
    private $_parent;
    private $_tasksNs;
    function __construct(array $arr, PEAR2_Pyrus_IPackageFile $parent)
    {
        $this->_parent = $parent;
        $this->_tasksNs = $this->_parent->getTasksNs();
        parent::__construct($this->_inner = new ArrayIterator($arr));
    }

    function accept()
    {
        if (!$this->_inner->valid()) {
            return false;
        }

        $file = $this->key();
        foreach ($this->_inner->current() as $key => $value) {
            if (strpos($key, $this->_tasksNs . ':') !== 0) {
                continue;
            }

            $task = str_replace(array($this->_tasksNs . ':', '-'), array('', ' '), $key);
            $task = str_replace(' ', '/', ucwords($task));
            $task = str_replace('/', '_', $task);
            $class = 'PEAR2_Pyrus_Task_' . $task;
            if (!class_exists($class, true)) {
                continue;
            }
            if ($class::TYPE == 'script') {
                return true;
            }
        }
        return false;
    }

    function current()
    {
        return $this->_parent->files[$this->_inner->key()];
    }
}