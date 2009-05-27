<?php
/**
 * <tasks:postinstallscript> paramgroup param object
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
 * Implements the postinstallscript file task <param> tag
 *
 * Sample usage:
 *
 * <code>
 * $task->paramgroup['nameid']->instructions('hi there')
 *  ->condition($task->paramgroup['previous']->param['paramname'], '>=', '25')
 *  ->param['paramname']->prompt('blah')->type('string')->defaultValue('hi');
 * </code>
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Task_Postinstallscript_Paramgroup_Param implements ArrayAccess, Iterator, Countable
{
    protected $info;
    protected $index = null;
    protected $parent;
    protected $tasksNs;

    function __construct($tasksNs, $parent, array $info, $index = null)
    {
        if ($tasksNs) {
            if ($tasksNs[strlen($tasksNs)-1] != ':') {
                $tasksNs .= ':';
            }
        } else {
            $tasksNs = '';
        }
        $this->tasksNs = $tasksNs;
        $this->parent = $parent;
        $this->info = $info;
        $this->index = $index;
    }

    function count()
    {
        return count($this->info);
    }

    function current()
    {
        $i = key($this->info);
        foreach (array('name', 'prompt', 'type', 'default') as $key) {
            if (!array_key_exists($this->tasksNs . $key, $this->info[$i])) {
                $this->info[$i][$this->tasksNs . $key] = null;
            }
        }
        return new self($this->tasksNs, $this, $this->info[$i], $i);
    }

    function rewind()
    {
        reset($this->info);
    }

    function key()
    {
        $i = key($this->info);
        return $this->info[$i][$this->tasksNs . 'id'];
    }

    function next()
    {
        return next($this->info);
    }

    function valid()
    {
        return current($this->info);
    }

    function locateParam($name)
    {
        foreach ($this->info as $i => $param)
        {
            if (isset($param[$this->tasksNs . 'name']) && $param[$this->tasksNs . 'name'] == $name) {
                return $i;
            }
        }
        return false;
    }

    function offsetGet($var)
    {
        if (isset($this->index)) {
            throw new PEAR2_Pyrus_Task_Exception('Use -> operator to access param properties');
        }
        $i = $this->locateParam($var);
        if (false === $i) {
            $i = count($this->info);
            $this->info[$i] = array($this->tasksNs . 'name' => $var,
                                    $this->tasksNs . 'prompt' => null,
                                    $this->tasksNs . 'type' => null,
                                    $this->tasksNs . 'default' => null);
        } else {
            foreach (array('name', 'prompt', 'type', 'default') as $key) {
                if (!array_key_exists($this->tasksNs . $key, $this->info[$i])) {
                    $this->info[$i][$this->tasksNs . $key] = null;
                }
            }
        }
        return new self($this->tasksNs, $this, $this->info[$i], $i);
    }

    function offsetSet($var, $value)
    {
        if (isset($this->index)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Use -> operator to access param properties');
        }
        if (!($value instanceof self)) {
            throw new PEAR2_Pyrus_Task_Exception('Can only set $param[\'' .
                $var .
                '\'] to PEAR2_Pyrus_Task_Postinstallscript_Paramgroup_Param object');
        }
        if ($var === null) {
            $var = $value->id;
        }
        if ($value->id != $var) {
            throw new PEAR2_Pyrus_Task_Exception('Cannot set ' .
                $var . ' to ' .
                $value->id .
                ', use $param[] to set a new value');
        }
        if (false === ($i = $this->locateParam($var))) {
            $i = count($this->info);
        }
        $this->info[$i] = $value->getInfo();
        $this->save();
    }

    function offsetExists($var)
    {
        if (isset($this->index)) {
            throw new PEAR2_Pyrus_Task_Exception('Use -> operator to access param properties');
        }
        $i = $this->locateParam($var);
        return $i !== false;
    }

    function offsetUnset($var)
    {
        if (isset($this->index)) {
            throw new PEAR2_Pyrus_Task_Exception('Use -> operator to access param properties');
        }
        $i = $this->locateParam($var);
        if ($i === false) {
            return;
        }
        unset($this->info[$i]);
        $this->info = array_values($this->info);
        $this->save();
    }

    function __get($var)
    {
        if ($var === 'paramgroup') {
            if ($this->parent instanceof self) {
                return $this->parent->paramgroup;
            }
            return $this->parent;
        }
        if (!isset($this->index)) {
            throw new PEAR2_Pyrus_Task_Exception('Use [] operator to access params');
        }
        if (!array_key_exists($this->tasksNs . $var, $this->info)) {
            $info = array_keys($this->info);
            $a = $this->tasksNs;
            array_walk($info, function(&$key) use ($a) {$key = str_replace($a, '', $key);});
            throw new PEAR2_Pyrus_Task_Exception(
                'Unknown variable ' . $var . ', should be one of ' . implode(', ', $info)
            );
        }
        return $this->info[$this->tasksNs . $var];
    }

    function __isset($var)
    {
        if (!isset($this->index)) {
            throw new PEAR2_Pyrus_Task_Exception('Use [] operator to access paramgroups');
        }
        if (!array_key_exists($this->tasksNs . $var, $this->info)) {
            $info = array_keys($this->info);
            $a = $this->tasksNs;
            array_walk($info, function(&$key) use ($a) {$key = str_replace($a, '', $key);});
            throw new PEAR2_Pyrus_Task_Exception(
                'Unknown variable ' . $var . ', should be one of ' . implode(', ', $info)
            );
        }
        return isset($this->info[$this->tasksNs . $var]);
    }

    function __unset($var)
    {
        if (!isset($this->index)) {
            throw new PEAR2_Pyrus_Task_Exception('Use [] operator to access params');
        }
        if (!array_key_exists($this->tasksNs . $var, $this->info)) {
            $info = array_keys($this->info);
            $a = $this->tasksNs;
            array_walk($info, function(&$key) use ($a) {$key = str_replace($a, '', $key);});
            throw new PEAR2_Pyrus_Task_Exception(
                'Unknown variable ' . $var . ', should be one of ' . implode(', ', $info)
            );
        }
        $this->info[$this->tasksNs . $var] = null;
        $this->save();
    }

    function __set($var, $value)
    {
        return $this->__call($var, array($value));
    }

    function __call($var, $args)
    {
        if (!isset($this->index)) {
            throw new PEAR2_Pyrus_Task_Exception('Use [] operator to access params');
        }
        if (!array_key_exists($this->tasksNs . $var, $this->info)) {
            $info = array_keys($this->info);
            $a = $this->tasksNs;
            array_walk($info, function(&$key) use ($a) {$key = str_replace($a, '', $key);});
            throw new PEAR2_Pyrus_Task_Exception(
                'Unknown variable ' . $var . ', should be one of ' . implode(', ', $info)
            );
        }
        if (!count($args) || $args[0] === null) {
            $this->info[$this->tasksNs . $var] = null;
            $this->save();
            return $this;
        }
        $this->info[$this->tasksNs . $var] = $args[0];
        $this->save();
        return $this;
    }

    function getInfo()
    {
        return $this->info;
    }

    function setInfo($index, $info)
    {
        foreach ($info as $key => $null) {
            if ($null === null) {
                unset($info[$key]);
                continue;
            }
            if (is_array($null) && count($null) == 1) {
                $info[$key] = $null[0];
            }
        }
        $this->info[$index] = $info;
    }

    function save()
    {
        if ($this->parent instanceof self) {
            $this->parent->setInfo($this->index, $this->info);
        } else {
            $info = $this->info;
            if (!count($info)) {
                $info = null;
            }
            $this->parent->setParams($info);
        }
        $this->parent->save();
    }
}
?>