<?php
/**
 * PEAR2_Pyrus_Package_Creator_TaskIterator
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
 * Class which iterates over all the tasks to perform for package creation.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Package_Creator_TaskIterator extends FilterIterator
{
    private $_inner;
    private $_parent;
    private $_tasksNs;
    function __construct(array $arr, PEAR2_Pyrus_Package $parent)
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

        if ($this->_inner->key() == 'attribs') {
            return false;
        }

        $key = $this->key();
        if (strpos($key, $this->_tasksNs . ':') !== 0) {
            return false;
        }

        return true;
    }

    function current()
    {
        $xml = parent::current();
        $task = 'PEAR2_Pyrus_Task_' .
            ucfirst(str_replace($this->_tasksNs . ':', '', $this->key()));
        $a = new $task(PEAR2_Pyrus_Config::current(), PEAR2_Pyrus_Task_Common::PACKAGE);
        return array($xml, $a);
    }
}