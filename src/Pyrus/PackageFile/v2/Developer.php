<?php
/**
 * \Pyrus\PackageFile\v2\Developer
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * Manage an individual maintainer in package.xml
 *
 * To be used like:
 *
 * <code>
 * // add developer
 * $pf->maintainer['cellog']
 *    ->name('Greg Beaver')
 *    ->role('lead')
 *    ->email('cellog@php.net')
 *    ->active('yes');
 * echo $pf->maintainer['cellog']->name;
 * isset($pf->maintainer['cellog']); // test for maintainer in package.xml
 * unset($pf->maintainer['cellog']); // remove from package.xml
 * </code>
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\PackageFile\v2;
class Developer implements \ArrayAccess, \Iterator
{
    protected $parent;
    protected $role;
    protected $index;
    protected $info;
    private $_curRole;

    function __construct($parent, $info, $role = null, $index = null)
    {
        $this->parent = $parent;
        $this->info = $info;
        $this->role = $role;
        $this->index = $index;
    }

    function current()
    {
        $info = current($this->info[current($this->_curRole)]);
        foreach(array('name', 'user', 'email', 'active') as $key) {
            if (!array_key_exists($key, $info)) {
                $info[$key] = null;
            }
        }

        return new Developer($this, $info, current($this->_curRole),
                            key($this->info[current($this->_curRole)]));
    }

    function key()
    {
        $info = current($this->info[current($this->_curRole)]);
        return $info['user'];
    }

    function next()
    {
        $a = next($this->info[current($this->_curRole)]);
        while (!current($this->info[current($this->_curRole)])) {
            next($this->_curRole);
            if (!current($this->_curRole)) {
                return false;
            }

            reset($this->info[current($this->_curRole)]);
            if (count($this->info[current($this->_curRole)])) {
                return true;
            }
        }

        return $a;
    }

    function rewind()
    {
        $this->_curRole = array_keys($this->info);
        reset($this->info[current($this->_curRole)]);
    }

    function valid()
    {
        while (current($this->_curRole) && !current($this->info[current($this->_curRole)])) {
            next($this->_curRole);
            if (!current($this->_curRole)) {
                return false;
            }

            reset($this->info[current($this->_curRole)]);
            if (count($this->info[current($this->_curRole)])) {
                return true;
            }
        }

        return current($this->_curRole);
    }

    /**
     * Search for a maintainer, find them and return the maintainer role
     *
     * @param string $handle
     * @return array|false the role (lead, developer, contributor, helper)/index
     */
    function locateMaintainerRole($handle)
    {
        foreach ($this->info as $role => $devs) {
            foreach ($devs as $i => $developer) {
                if ($developer['user'] == $handle) {
                    return array($role, $i);
                }
            }
        }
        return false;
    }

    function __get($var)
    {
        if (!isset($this->info['user']) || !is_string($this->info['user'])) {
            throw new Developer\Exception(
                'Cannot access developer info for unknown developer');
        }

        if ($var === 'role') {
            return $this->role;
        }

        if (!isset($this->info[$var])) {
            if (!array_key_exists($var, $this->info)) {
                $keys = $this->info;
                unset($keys['user']);
                $keys = array_keys($keys);
                throw new Developer\Exception(
                    'Unknown variable ' . $var . ', should be one of ' . implode(', ', $keys));
            }

            return null;
        }

        return $this->info[$var];
    }

    function __call($var, $args)
    {
        if (!isset($this->info['user']) || !is_string($this->info['user'])) {
            throw new Developer\Exception(
                'Cannot set developer info for unknown developer');
        }

        if ($var == 'role') {
            $oldrole = $this->role;
            $this->role = $args[0];
            $this->save($oldrole);
            return $this;
        }

        if (!array_key_exists($var, $this->info) || $var == 'user') {
            throw new Developer\Exception(
                'Cannot set unknown value ' . $var);
        }

        if (count($args) != 1) {
            throw new Developer\Exception(
                'Can only set ' . $var . ' to 1 value');
        }

        if (!is_string($args[0])) {
            throw new Developer\Exception(
                'Invalid value for ' . $var . ', must be a string');
        }

        $this->info[$var] = $args[0];
        $this->save($this->role);
        return $this;
    }

    function offsetGet($var)
    {
        if (isset($this->info['user']) && is_string($this->info['user'])) {
            throw new Developer\Exception(
                'Use -> to access properties of a developer');
        }

        if (!is_string($var)) {
            throw new Developer\Exception('Developer handle cannot be numeric');
        }

        $developer = $var;
        if ($role = $this->locateMaintainerRole($developer)) {
            $info = $this->info[$role[0]][$role[1]];
            foreach (array('name' => null, 'user' => $var, 'email' => null, 'active' => 'yes') as $key => $null) {
                if (!isset($info[$key])) {
                    $info[$key] = null;
                }
            }

            return new Developer($this, $info, $role[0], $role[1]);
        }

        return new Developer($this,
            array('name' => null, 'user' => $var, 'email' => null, 'active' => 'yes'), null, null);
    }

    function offsetSet($var, $value)
    {
        if (isset($this->info['user']) && is_string($this->info['user'])) {
            throw new Developer\Exception(
                'Use -> to access properties of a developer');
        }

        if (!is_string($var)) {
            throw new Developer\Exception('Developer handle cannot be numeric');
        }

        if (!($value instanceof Developer)) {
            throw new Developer\Exception(
                'Can only set a developer to a \Pyrus\PackageFile\v2\Developer object'
            );
        }

        if (false !== ($i = $this->locateMaintainerRole($var))) {
            // remove old developer role, set new role
            unset($this->info[$i[0]][$i[1]]);
        }

        $i = count($this->info[$value->role]);
        $this->info[$value->role][] = $value->getInfo();
        $this->info[$value->role][$i]['user'] = $var;
        $this->save();
    }

    /**
     * Remove a developer from package.xml (by handle)
     * @param string $var
     */
    function offsetUnset($var)
    {
        if (isset($this->info['user']) && is_string($this->info['user'])) {
            throw new Developer\Exception(
                'Use -> to retrieve properties of a developer');
        }

        // remove developer
        $role = $this->locateMaintainerRole($var);
        if (!$role) {
            // already non-existent
            return;
        }

        unset($this->info[$role[0]][$role[1]]);
        $this->save();
    }

    /**
     * Test whether developer exists in package.xml (by handle)
     * @param string $var
     * @return bool
     */
    function offsetExists($var)
    {
        if (isset($this->info['user']) && is_string($this->info['user'])) {
            throw new Developer\Exception(
                'Use -> to retrieve properties of a developer');
        }

        return (bool) $this->locateMaintainerRole($var);
    }

    /**
     * Retrieve the new index of this developer, newly added to this role
     * @return int
     */
    function getNewIndex($user, $role)
    {
        $i = $this->locateMaintainerRole($user);
        if (false === $i) {
            return count($this->info[$role]) - 1;
        }

        return $i[1];
    }

    function getInfo()
    {
        return $this->info;
    }

    function toArray()
    {
        $info = $this->info;
        $ret = array('name' => null, 'user' => null, 'email' => null, 'active' => null);
        foreach ($info as $key => $value) {
            $ret[$key] = $value;
        }

        return $ret;
    }

    function setInfo($info, $oldrole, $index, $role)
    {
        foreach (array_keys($info) as $key) {
            if ($info[$key] === null) {
                unset($info[$key]);
            }
        }

        if ($role !== null && $oldrole != $role) {
            // we just changed the role.
            if ($oldrole && isset($this->info[$oldrole][$index])) {
                unset($this->info[$oldrole][$index]);
                $this->info[$oldrole] = array_values($this->info[$oldrole]);
            }

            if (!count($info)) {
                // essentially remove the old one and wait for data to save the new
                return;
            }

            $this->info[$role][] = $info;
        } else {
            $this->info[$role][$index] = $info;
        }
    }

    /**
     * Save changes
     */
    protected function save($oldrole = null)
    {
        if ($this->parent instanceof self) {
            if ($this->role === null) {
                return;
            }

            $this->parent->setInfo($this->info, $oldrole, $this->index, $this->role);
            if ($this->role !== null && $oldrole != $this->role) {
                $this->index = $this->parent->getNewIndex($this->info['user'], $this->role);
            }

            $this->parent->save();
        } else {
            foreach ($this->info as $role => $info) {
                if (is_string($info)) {
                    $info = array($info);
                } else {
                    $info = array_values($info);
                }

                if (!count($info)) {
                    $this->parent->{'raw' . $role} = null;
                } else {
                    if (count($info) == 1) {
                        $info = $info[0];
                    }

                    $this->parent->{'raw' . $role} = $info;
                }
            }
        }
    }
}