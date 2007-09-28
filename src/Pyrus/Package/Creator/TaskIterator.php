<?php
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
        $a = new $task(PEAR2_Pyrus_Config::current(), PEAR2_PYRUS_TASK_PACKAGE);
        return array($xml, $a);
    }
}