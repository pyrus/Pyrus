<?php
/**
 * \Pyrus\Package\Creator\TaskIterator
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * Class which iterates over all the tasks to perform for package creation.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\Package\Creator;
class TaskIterator extends \FilterIterator
{
    private $_inner;
    private $_parent;
    private $_tasksNs;
    private $_installphase;
    protected $lastversion;

    function __construct(array $arr, \Pyrus\PackageInterface $parent, $phase, $lastversion = null)
    {
        $this->_parent = $parent;
        $this->_tasksNs = $this->_parent->getTasksNs();
        $this->_installphase = $phase;
        $this->lastversion = $lastversion;
        parent::__construct($this->_inner = new \ArrayIterator($arr));
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

        $task = \Pyrus\Task\Common::getTask($key);
        if ($task === false) {
            throw new \RuntimeException("Unknown task `$key` specified.");
        }
		
        if (0 == $task::PHASE & $this->_installphase) {
            // skip tasks that won't run in this installphase
            return false;
        }

        if ($this->_installphase == \Pyrus\Task\Common::INSTALL && $this->_parent->isPreProcessed()) {
            $info = $this->current();
            if ($info->isPreProcessed()) {
                // some tasks are pre-processed at package-time
                return false;
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
        if (isset($xml[0])) {
            $tasks = array();
            $task = str_replace(array($this->_tasksNs . ':', '-'), array('', ' '), parent::key());
            $task = str_replace(' ', '/', ucwords($task));
            $task = str_replace('/', '_', $task);
            $task = '\Pyrus\Task\\' . $task;
            foreach ($xml as $info) {
                $attribs = array();
                if (isset($xml['attribs'])) {
                    $attribs = $xml['attribs'];
                }

                $tasks[] = new $task($this->_parent, $this->_installphase, $info, $attribs, $this->lastversion);
            }

            $attribs = isset($this->_inner['attribs']) ? array($this->_inner['attribs']) : $this->_inner;
            // use proxy for multiple tasks
            return new \Pyrus\Task\MultipleProxy($this->_parent, $tasks, $attribs, $this->key());
        }

        $attribs = array();
        if (isset($xml['attribs'])) {
            $attribs = $xml['attribs'];
        }

        $task = \Pyrus\Task\Common::getTask(parent::key());
        if ($task === false) {
            throw new \RuntimeException('Unknown task `'.parent::key().'` specified.');
        }
        return new $task($this->_parent, $this->_installphase, $xml, $attribs, $this->lastversion);
    }
}