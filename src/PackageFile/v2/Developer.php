<?php
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
 */
class PEAR2_Pyrus_PackageFile_v2_Developer implements ArrayAccess
{
    private $_packageInfo;
    private $_developer = null;
    private $_role = null;
    private $_info = array('name' => null, 'user' => null, 'email' => null, 'active' => null);
    function __construct(array &$parent, $developer = null)
    {
        $this->_packageInfo = &$parent;
        if ($developer) {
            $this->_developer = $developer;
            $this->_info['user'] = $developer;
        }
    }

    /**
     * Search for a maintainer, find them and return the maintainer role
     *
     * @param string $handle
     * @return string|false the role (lead, developer, contributor, helper)
     */
    function locateMaintainerRole($handle)
    {
        foreach (array('lead', 'developer', 'contributor', 'helper') as $role) {
            if (!isset($this->_packageInfo[$role])) continue;
            $inf = $this->_packageInfo[$role];
            if (!isset($inf[0])) $inf = array($inf);
            foreach ($inf as $i) {
                if ($i['user'] == $handle) return $role;
            }
        }
        return false;
    }

    function __get($var)
    {
        if ($this->_developer === null) {
            throw new PEAR2_Pyrus_PackageFile_v2_Developer_Exception(
                'Cannnot access developer info for unknown developer');
        }
        if (!isset($this->_info[$var])) {
            return null;
        }
        return $this->_info[$var];
    }

    function __call($var, $args)
    {
        if ($this->_developer === null) {
            throw new PEAR2_Pyrus_PackageFile_v2_Developer_Exception(
                'Cannnot set developer info for unknown developer');
        }
        if (!isset($this->_info[$var]) || $var == 'user') {
            throw new PEAR2_Pyrus_PackageFile_v2_Developer_Exception(
                'Cannot set unknown value ' . $var);
        }
        if (count($args) != 1 || !is_string($args[0])) {
            throw new PEAR2_Pyrus_PackageFile_v2_Developer_Exception(
                'Invalid value for ' . $var);
        }
        $this->_info[$var] = $args[0];
        $this->_save();
        return $this;
    }

    function offsetGet($var)
    {
        return new PEAR2_Pyrus_PackageFile_v2_Developer($this->_packageInfo, $var);
    }

    function offsetSet($var, $value)
    {
        $this->_developer = $var;
        $this->_info['user'] = $var;
        if ($var instanceof PEAR2_Pyrus_PackageFile_v2_Developer) {
            $this->_info['name'] = $value->name;
            $this->_info['email'] = $value->email;
            $this->_info['active'] = $value->active;
        }
        if (is_array($value) || $value instanceof ArrayObject) {
            if (!isset($value['name']) || !isset($value['email']) || !isset($value['active'])) {
                throw new PEAR2_Pyrus_PackageFile_v2_Developer_Exception(
                    'Invalid array used to set ' . $this->_developer . ' information');
            }
            $this->_info['name'] = $value['name'];
            $this->_info['email'] = $value['email'];
            $this->_info['active'] = $value['active'];
        }
        $this->_save();
    }

    /**
     * Remove a developer from package.xml (by handle)
     * @param string $var
     */
    function offsetUnset($var)
    {
        // remove developer
        $role = $this->locateMaintainerRole($var);
        if (!$role) {
            // already non-existent
            return;
        }
        if (count($this->_packageInfo[$role]) == 1) {
            unset($this->_packageInfo[$role]);
            return;
        }
        foreach ($this->_packageInfo[$role] as $i => $stuff) {
            if ($stuff['user'] == $var) {
                unset($this->_packageInfo[$role][$i]);
                if (count($this->_packageInfo[$role]) == 1) {
                    $this->_packageInfo[$role] = $this->_packageInfo[$role][0];
                }
                return;
            }
        }
    }

    /**
     * Test whether developer exists in package.xml (by handle)
     * @param string $var
     * @return bool
     */
    function offsetExists($var)
    {
        return (bool) $this->locateMaintainerRole($var);
    }

    /**
     * Save changes
     */
    private function _save()
    {
        $role = $this->locateMaintainerRole($this->_developer);
        if (!$role) {
            // create new
            if (!isset($this->_packageInfo[$this->_role])) {
                $this->_packageInfo[$this->_role] = $this->_info;
                return;
            }
            if (!isset($this->_packageInfo[$this->_role][0])) {
                $this->_packageInfo[$this->_role] = array($this->_packageInfo[$this->_role],
                    $this->_info);
            }
            $this->_packageInfo[$this->_role][] = $this->_info;
            return;
        }
        // remove the maintainer from their old role
        if ($role !== $this->_role) {
            if (!isset($this->_packageInfo[$role][0])) {
                unset($this->_packageInfo[$role]);
            } else {
                foreach ($this->_packageInfo[$role] as $i => $dev) {
                    if ($dev['user'] == $this->_developer) {
                        unset($this->_packageInfo[$role][$i]);
                        if (count($this->_packageInfo[$role]) == 1) {
                            $this->_packageInfo[$role] = $this->_packageInfo[$role][0];
                        } else {
                            $this->_packageInfo[$role] =
                                array_values($this->_packageInfo[$role]);
                        }
                    }
                }
            }
        }
        if (!isset($this->_packageInfo[$this->_role])) {
            $this->_packageInfo[$this->_role] = $this->_info;
            return;
        }
        if (!isset($this->_packageInfo[$this->_role][0])) {
            if ($role !== $this->_role) {
                // We are a new entry into this role, and now there are 2 of us
                $this->_packageInfo[$this->_role] =
                    array($this->_packageInfo[$this->_role], $this->_info);
            } else {
                // We are replacing ourself
                $this->_packageInfo[$this->_role] = $this->_info;
            }
        } else {
            if ($role !== $this->_role) {
                // We are a new entry into this role, and now there are several of us
                $this->_packageInfo[$this->_role][] = $this->_info;
            } else {
                foreach ($this->_packageInfo[$role] as $i => $maybeme) {
                    if ($maybeme['user'] == $this->_developer) {
                        // found our entry
                        $this->_packageInfo[$role][$i] = $this->_info;
                        return;
                    }
                }
            }
        }
    }
}