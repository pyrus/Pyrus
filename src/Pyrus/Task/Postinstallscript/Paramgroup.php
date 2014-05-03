<?php
/**
 * <tasks:postinstallscript> paramgroup object
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

namespace Pyrus\Task\Postinstallscript;

/**
 * Implements the postinstallscript file task <paramgroup> tag
 *
 * Sample usage:
 *
 * <code>
 * $task->paramgroup['nameid']->instructions('hi there')
 *  ->condition($task->paramgroup['previous']->param['paramname'], '>=', '25')
 *  ->param['paramname']->prompt('blah')->type('string')->defaultValue('hi');
 * </code>
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class Paramgroup implements \ArrayAccess, \Iterator, \Countable
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

    /**
     * Determines whether the <conditiontype> tag in this paramgroup is satisfied by
     * the last <paramgroup>.
     *
     * If this is a simple paramgroup with no conditiontype, this always returns true.
     * Otherwise, this processes the conditiontype, and determines whether the variable
     * specified was satisfied by the last executed paramgroup
     * @return bool
     */
    function matchesConditionType(array $answers = null)
    {
        if (!isset($this->name)) {
            return true;
        }
        if (!isset($answers)) {
            throw new \Pyrus\Task\Exception('Invalid post-install script, <conditiontype> can only ' .
                                                 'be used if the previous paramgroup has prompts');
        }

        if (isset($answers[$this->name])) {
            switch ($this->conditiontype) {
                case '=' :
                    if ($answers[$this->name] == $this->value) {
                        return true;
                    }
                break;
                case '!=' :
                    if ($answers[$this->name] != $this->value) {
                        return true;
                    }
                break;
                case 'preg_match' :
                    if (@preg_match('/' . $this->value . '/',
                          $answers[$this->name])) {
                        return true;
                    }
                break;
                default :
                return false;
            }
        }
        return false;
    }

    function count()
    {
        return count($this->info);
    }

    function current()
    {
        $i = key($this->info);
        foreach (array('id', 'instructions', 'name', 'conditiontype', 'value', 'param') as $key) {
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

    function locateParamgroup($id)
    {
        foreach ($this->info as $i => $paramgroup)
        {
            if (isset($paramgroup[$this->tasksNs . 'id']) && $paramgroup[$this->tasksNs . 'id'] == $id) {
                return $i;
            }
        }
        return false;
    }

    function offsetGet($var)
    {
        if (isset($this->index)) {
            throw new \Pyrus\Task\Exception('Use -> operator to access paramgroup properties');
        }
        $i = $this->locateParamgroup($var);
        if (false === $i) {
            $i = count($this->info);
            $this->info[$i] = array($this->tasksNs . 'id' => $var,
                                    $this->tasksNs . 'instructions' => null,
                                    $this->tasksNs . 'name' => null,
                                    $this->tasksNs . 'conditiontype' => null,
                                    $this->tasksNs . 'value' => null,
                                    $this->tasksNs . 'param' => null);
        } else {
            foreach (array('id', 'instructions', 'name', 'conditiontype', 'value', 'param') as $key) {
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
            throw new \Pyrus\Task\Exception('Use -> operator to access paramgroup properties');
        }
        if (!($value instanceof self)) {
            throw new \Pyrus\Task\Exception('Can only set $paramgroup[\'' .
                $var .
                '\'] to \Pyrus\Task\Postinstallscript\Paramgroup object');
        }
        if ($var === null) {
            $var = $value->id;
        }
        if ($value->id != $var) {
            throw new \Pyrus\Task\Exception('Cannot set ' .
                $var . ' to ' .
                $value->id .
                ', use $paramgroup[] to set a new value');
        }
        if (false === ($i = $this->locateParamgroup($var))) {
            $i = count($this->info);
        }
        $this->info[$i] = $value->getInfo();
        $this->save();
    }

    function offsetExists($var)
    {
        if (isset($this->index)) {
            throw new \Pyrus\Task\Exception('Use -> operator to access paramgroup properties');
        }
        $i = $this->locateParamgroup($var);
        return $i !== false;
    }

    function offsetUnset($var)
    {
        if (isset($this->index)) {
            throw new \Pyrus\Task\Exception('Use -> operator to access paramgroup properties');
        }
        $i = $this->locateParamgroup($var);
        if ($i === false) {
            return;
        }
        unset($this->info[$i]);
        $this->info = array_values($this->info);
        $this->save();
    }

    function __get($var)
    {
        if (!isset($this->index)) {
            throw new \Pyrus\Task\Exception('Use [] operator to access paramgroups');
        }
        if (!array_key_exists($this->tasksNs . $var, $this->info)) {
            $info = array_keys($this->info);
            $a = $this->tasksNs;
            array_walk($info, function(&$key) use ($a) {$key = str_replace($a, '', $key);});
            throw new \Pyrus\Task\Exception(
                'Unknown variable ' . $var . ', should be one of ' . implode(', ', $info)
            );
        }
        if ($var === 'param') {
            $ret = $this->info[$this->tasksNs . 'param'];
            if ($ret === null) {
                $ret = array();
            } elseif (is_array($ret) && !isset($ret[0])) {
                $ret = array($ret);
            }
            return new \Pyrus\Task\Postinstallscript\Paramgroup\Param($this->tasksNs, $this, $ret);
        }
        return $this->info[$this->tasksNs. $var];
    }

    function __isset($var)
    {
        if (!isset($this->index)) {
            throw new \Pyrus\Task\Exception('Use [] operator to access paramgroups');
        }
        if (!array_key_exists($this->tasksNs. $var, $this->info)) {
            $info = array_keys($this->info);
            $a = $this->tasksNs;
            array_walk($info, function(&$key) use ($a) {$key = str_replace($a, '', $key);});
            throw new \Pyrus\Task\Exception(
                'Unknown variable ' . $var . ', should be one of ' . implode(', ', $info)
            );
        }
        return isset($this->info[$this->tasksNs. $var]);
    }

    function __unset($var)
    {
        if (!isset($this->index)) {
            throw new \Pyrus\Task\Exception('Use [] operator to access paramgroups');
        }
        if (!array_key_exists($this->tasksNs. $var, $this->info)) {
            $info = array_keys($this->info);
            $a = $this->tasksNs;
            array_walk($info, function(&$key) use ($a) {$key = str_replace($a, '', $key);});
            throw new \Pyrus\Task\Exception(
                'Unknown variable ' . $var . ', should be one of ' . implode(', ', $info)
            );
        }
        $this->info[$this->tasksNs. $var] = null;
        $this->save();
    }

    function __set($var, $value)
    {
        return $this->__call($var, array($value));
    }

    function __call($var, $args)
    {
        if (!isset($this->index)) {
            throw new \Pyrus\Task\Exception('Use [] operator to access paramgroups');
        }
        if (!array_key_exists($this->tasksNs. $var, $this->info)) {
            $info = array_keys($this->info);
            $a = $this->tasksNs;
            array_walk($info, function(&$key) use ($a) {$key = str_replace($a, '', $key);});
            throw new \Pyrus\Task\Exception(
                'Unknown variable ' . $var . ', should be one of ' . implode(', ', $info)
            );
        }
        if (!count($args) || $args[0] === null) {
            $this->info[$this->tasksNs. $var] = null;
            $this->save();
            return $this;
        }
        if ($var == 'param') {
            if (!isset($this->info[$this->tasksNs. $var])) {
                $this->info[$this->tasksNs. $var] = $args;
            } else {
                if (!is_array($this->info[$this->tasksNs. $var])) {
                    $this->info[$this->tasksNs. $var] = array($this->info[$this->tasksNs. $var]);
                }
                $this->info[$this->tasksNs. $var] = array_merge($this->info[$this->tasksNs. $var], $args);
            }
        } else {
            if ($var === 'name') {
                if (!strpos($args[0], '::')) {
                    throw new \Pyrus\Task\Exception('name must reference a previous paramgroup\'s param with ' .
                                                         'syntax paramgroup::paramname');
                }
                $name = explode('::', $args[0]);
                if (count($name) != 2) {
                    throw new \Pyrus\Task\Exception('name must reference a previous paramgroup\'s param with ' .
                                                         'syntax paramgroup::paramname');
                }
                if ($name[0] == $this->id) {
                    throw new \Pyrus\Task\Exception('name must reference a previous paramgroup\'s param.  ' .
                                                         $args[0] . ' references this paramgroup');
                }
                if (!isset($this->parent[$name[0]])) {
                    throw new \Pyrus\Task\Exception('name must reference a previous paramgroup\'s param.  ' .
                                                         'paramgroup "' . $name[0] . '" is not yet created');
                }
                if (!isset($this->parent[$name[0]]->param[$name[1]])) {
                    throw new \Pyrus\Task\Exception('name must reference a previous paramgroup\'s param.  ' .
                                                         'paramgroup "' . $name[0] . '" param "' .
                                                         $name[1] . '" is not yet created');
                }
            }
            $this->info[$this->tasksNs. $var] = $args[0];
        }
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

    function setParams($info)
    {
        if ($info === null) {
            unset($this->info[$this->tasksNs. 'param']);
            return;
        }
        if (count($info) == 1) {
            $info = $info[0];
        }
        $this->info[$this->tasksNs. 'param'] = $info;
    }

    /**
     * Set the condition for this paramgroup
     * @param \Pyrus\Task\Postinstallscript\Paramgroup\Param $parameter the
     *        previous parameter whose value we use for the condition check
     * @param string $operator one of =, !=, or preg_match
     * @param string $value the value to match the parameter against
     */
    function condition(\Pyrus\Task\Postinstallscript\Paramgroup\Param $parameter = null, $operator = null,
                       $value = null)
    {
        if (!isset($this->index)) {
            throw new \Pyrus\Task\Exception('Use [] operator to access paramgroups');
        }
        if ($parameter === null) {
            $this->name = null;
            $this->conditiontype = null;
            $this->value = null;
            return;
        }
        $this->name = $parameter->paramgroup->id . '::' . $parameter->name;
        if ($operator != '=' && $operator != '!=' && $operator != 'preg_match') {
            throw new \Pyrus\Task\Exception('Invalid operator for post-install script condition, ' .
                                                 ' must be one of =, != or preg_match');
        }
        $this->conditiontype = $operator;
        $this->value = $value;
    }

    function save()
    {
        if ($this->parent instanceof self) {
            $this->parent->setInfo($this->index, $this->info);
            $this->parent->save();
        } else {
            $info = $this->info;
            if (count($info) == 1) {
                $info = $info[0];
            } elseif (!count($info)) {
                $info = null;
            }
            $this->parent->setParamgroups($info);
        }
    }
}
?>