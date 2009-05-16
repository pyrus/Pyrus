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
    private $_installphase;
    protected $lastversion;

    function __construct(array $arr, PEAR2_Pyrus_Package $parent, $phase, $lastversion = null)
    {
        $this->_parent = $parent;
        $this->_tasksNs = $this->_parent->getTasksNs();
        $this->_installphase = $phase;
        $this->lastversion = $lastversion;
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

        $key = parent::key();
        if (strpos($key, $this->_tasksNs . ':') !== 0) {
            return false;
        }

        $xml = parent::current();
        $task = 'PEAR2_Pyrus_Task_' . ucfirst(str_replace($this->_tasksNs . ':', '', parent::key()));

        if (0 == $task::PHASE & $this->_installphase) {
            // skip tasks that won't run in this installphase
            return false;
        }

        if ($this->_installphase == PEAR2_Pyrus_Task_Common::INSTALL && $this->_parent->isPreProcessed()) {
            $info = $this->current();
            if ($info[1] instanceof PEAR2_Pyrus_Task_Replace) {
                if ($info[0]['attribs']['type'] == 'package-info') {
                    // for pre-processed packages, package-info replacements are
                    // done at packaging time, so we don't need to re-do these
                    return false;
                }
            }
        }
        return true;
    }

    function key()
    {
        return str_replace($this->_tasksNs . ':', '', parent::key());
    }

    function current()
    {
        $xml = parent::current();
        $attribs = array();
        if (isset($xml['attribs'])) {
            $attribs = $xml['attribs'];
        }
        $task = 'PEAR2_Pyrus_Task_' .
            ucfirst(str_replace($this->_tasksNs . ':', '', parent::key()));
        $a = new $task($this->_installphase, $xml, $attribs, $this->lastversion);
        return array($xml, $a);
    }
}