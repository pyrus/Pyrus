<?php
/**
 * PEAR2_Pyrus_PackageFile_v2_Release
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
 * Manage a release in package.xml
 *
 * To be used like:
 *
 * <code>
 * // release is a PHP package
 * $pf->type = 'php';
 * // release is an extension source package
 * $pf->type = 'extsrc';
 * $pf->release[0]->installconditions['php']->min('5.2.0');
 * // defaults to "min"
 * $pf->release[0]->installconditions['php'] = '5.2.0';
 * // defaults to "pattern"
 * $pf->release[0]->installconditions['arch'] = 'i386';
 * $pf->release[0]->installconditions['arch']->pattern('i386')->conflicts();
 * // defaults to "name"
 * $pf->release[0]->installconditions['os'] = 'windows';
 * // defaults to existing
 * $pf->release[0]->installconditions['extension'][0]->name('PDO');
 * $pf->release[0]->installconditions['extension'][0]->name('PDO')->min('1.0');
 * $pf->release[0]->ignore('path/to/file.ext');
 * $pf->release[0]->installAs('path/to/anotherfile.ext', 'new/name.php');
 * // add another release
 * $i = count($pf->release);
 * $pf->release[$i]->ignore('path/to/anotherfile.ext');
 * $pf->release[$i]->installAs('path/to/file.ext', 'new/name.php');
 * // remove release
 * unset($pf->release[1]);
 * // remove all releases
 * $pf->release = null;
 * </code>
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_PackageFile_v2_Release implements ArrayAccess, Countable
{
    private $_parent;
    private $_packageInfo;
    private $_filelist;
    protected $index;
    protected $info = array();
    function __construct($parent, $packageInfo, array $filelist, $index = null)
    {
        $this->_parent = $parent;
        $this->_packageInfo = $packageInfo;
        $this->_filelist = $filelist;
        if ($index !== null) {
            $this->index = $index;
            $this->info = $packageInfo;
        } else {
            if (isset($packageInfo[0])) {
                $this->info = $packageInfo;
            } else if (count($packageInfo)) {
                $this->info = array($packageInfo);
            }
        }
    }

    function __get($var)
    {
        if (!isset($this->index)) {
            return null;
        }
        if ($var === 'installconditions') {
            if (!isset($this->info['installcondition'])) {
                $this->info['installcondition'] = array();
            }
            $conditions =
                new PEAR2_Pyrus_PackageFile_v2_Release_InstallCondition($this, $this->info['installcondition']);
            return $conditions;
        }
        throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Unknown variable ' . $var .
            ', installconditions is the only supported variable');
    }

    function count()
    {
        if (isset($this->index)) {
            return 1;
        }
        return count($this->info);
    }

    function offsetGet($var)
    {
        if (is_int($var) && !isset($this->index)) {
            if (isset($this->info[0])) {
                if (!isset($this->info[$var])) {
                    if ($var != count($this)) {
                        throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception(
                            'Can only set the ' .
                            'next highest release index ' . count($this) . ', not ' . $var);
                    }
                    $this->info[$var] = array();
                }
                return new PEAR2_Pyrus_PackageFile_v2_Release($this,
                           $this->info[$var], $this->_filelist, $var);
            } else {
                if ($var !== 0) {
                    throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Can only set the ' .
                        'next highest release index 0, not ' . $var);
                }
                $this->info[$var] = array();
                return new PEAR2_Pyrus_PackageFile_v2_Release($this,
                           $this->info[$var], $this->_filelist, $var);
            }
        }
    }

    /**
     * @return array
     */
    function getInstallCondition()
    {
        if (!isset($this->index)) {
            return null;
        }
        if (!isset($this->info['installcondition'])) {
            return false;
        }
        return $this->info['installcondition'];
    }

    function getInstallAs()
    {
        if (!isset($this->index)) {
            return null;
        }
        if (!isset($this->info['install'])) {
            return false;
        }
        return $this->info['install'];
    }

    function getIgnore()
    {
        if (!isset($this->index)) {
            return null;
        }
        if (!isset($this->info['ignore'])) {
            return false;
        }
        return $this->info['ignore'];
    }

    function ignores($file)
    {
        $ignore = $this->getIgnore();
        if (!$ignore) {
            return false;
        }
        if (!isset($ignore[0])) {
            $ignore = array($ignore);
        }
        foreach ($ignore as $ignored) {
            if ($ignored['attribs']['name'] == $file) {
                return true;
            }
        }
        return false;
    }

    function installsAs($file)
    {
        $install = $this->getInstallAs();
        if (!$install) {
            return $file;
        }
        if (!isset($install[0])) {
            $install = array($install);
        }
        foreach ($install as $as) {
            if ($as['attribs']['name'] == $file) {
                return $as['attribs']['as'];
            }
        }
        return $file;
    }

    function offsetSet($var, $value)
    {
        if ($var === null) {
            $var = count($this->info);
        }

        if (is_int($var)) {
            if ($value instanceof PEAR2_Pyrus_PackageFile_v2_Release) {
                $this->info[$var] = array('installcondition' => $value->getInstallCondition(),
                                          'install' => $value->getInstallAs(),
                                          'ignore' => $value->getIgnore());
                $this->save();
                return;
            }
            throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Cannot set ' . $var . ' to non-PEAR2_Pyrus_PackageFile_v2_Release');
        }
        throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Cannot set ' . $var);
    }

    /**
     * @param string $var
     */
    function offsetUnset($var)
    {
        unset($this->info[$var]);
        $this->save();
    }

    /**
     * @param string $var
     * @return bool
     */
    function offsetExists($var)
    {
        return isset($this->info[$var]);
    }

    function ignore($file)
    {
        if (!isset($this->index)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Cannot ignore ' .
                            'file ' . $file . ' without specifying which release section to ignore it in');
        }
        if (isset($this->_filelist[$file])) {
            if (!isset($this->info['ignore'])) {
                $this->info['ignore'] = array(array('attribs' => array('name' => $file)));
            } else {
                if (!isset($this->info['ignore'][0])) {
                    $this->info['ignore'] = array($this->info['ignore']);
                }
                $this->info['ignore'][] = array('attribs' => array('name' => $file));
            }
            $this->save();
            return;
        }

        throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Unknown file ' . $file .
            ' - add to filelist before ignoring');
    }

    function installAs($file, $newname)
    {
        if (!isset($this->index)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Cannot install ' .
                            'file ' . $file . ' to ' . $newname .
                            ' without specifying which release section to install it in');
        }

        if (isset($this->_filelist[$file])) {
            if (!isset($this->info['install'])) {
                $this->info['install'] = array(array('attribs' =>
                    array('name' => $file, 'as' => $newname)));
            } else {
                if (!isset($this->info['install'][0])) {
                    $this->info['install'] = array($this->info['install']);
                }
                $this->info['install'][] = array('attribs' =>
                    array('name' => $file, 'as' => $newname));
            }
            $this->save();
            return;
        }

        throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Unknown file ' . $file .
            ' - add to filelist before adding install as tag');
    }

    function setInstallCondition(PEAR2_Pyrus_PackageFile_v2_Release_InstallCondition $c)
    {
        $this->info['installcondition'] = $c->getInfo();
        $this->save();
    }

    function setReleaseInfo($index, $info)
    {
        $this->info[$index] = $info;
    }

    /**
     * Saves results to the parent packagefile object
     */
    protected function save()
    {
        if (isset($this->index)) {
            $this->_parent->setReleaseInfo($this->index, $this->info);
            return $this->_parent->save();
        }
        $newXml = $this->info;
        foreach ($newXml as $index => $info) {
            if (isset($info['ignore']) && count($info['ignore']) == 1 && !isset($info['ignore']['attribs'])) {
                $newXml[$index]['ignore'] = $newXml[$index]['ignore'][0];
            }
            if (isset($info['install']) && count($info['install']) == 1 && !isset($info['install']['attribs'])) {
                $newXml[$index]['install'] = $newXml[$index]['install'][0];
            }
            if (array_key_exists('ignore', $info) && !count($info['ignore'])) {
                unset($newXml[$index]['ignore']);
            }
            if (array_key_exists('install', $info) && !count($info['install'])) {
                unset($newXml[$index]['install']);
            }
            if (array_key_exists('installcondition', $info) && count($info['installcondition'])) {
                foreach (array('php', 'os', 'arch') as $key) {
                    if (!isset($info['installcondition'][$key])) {
                        continue;
                    }
                    foreach (array_keys($info['installcondition'][$key]) as $ikey) {
                        if ($info['installcondition'][$key][$ikey] === null) {
                            unset($newXml[$index]['installcondition'][$key][$ikey]);
                        }
                    }
                    if (!count($newXml[$index]['installcondition'][$key])) {
                        unset($newXml[$index]['installcondition'][$key]);
                    }
                }
                if (array_key_exists('extension', $info['installcondition']) && !count($info['installcondition']['extension'])) {
                    unset($newXml[$index]['installcondition']['extension']);
                }
                if (isset($info['installcondition']['extension'])) {
                    if (!isset($info['installcondition']['extension'][0])) {
                        $newXml[$index]['installcondition']['extension'] = $info['installcondition']['extension'] =
                            array($info['installcondition']['extension']);
                    }
                    foreach ($info['installcondition']['extension'] as $extkey => $ext) {
                        foreach (array_keys($ext) as $key) {
                            if ($ext[$key] === null) {
                                unset($newXml[$index]['installcondition']['extension'][$extkey][$key]);
                            }
                        }
                        if (!count($newXml[$index]['installcondition']['extension'][$extkey])) {
                            unset($newXml[$index]['installcondition']['extension'][$extkey]);
                        }
                    }
                    if (count($newXml[$index]['installcondition']['extension']) == 1) {
                        $newXml[$index]['installcondition']['extension'] =
                            $newXml[$index]['installcondition']['extension'][0];
                    } elseif (count($newXml[$index]['installcondition']['extension'] == 0)) {
                        unset($newXml[$index]['installcondition']['extension']);
                    }
                }

                if (!count($newXml[$index]['installcondition'])) {
                    unset($newXml[$index]['installcondition']);
                    continue;
                }
            } else {
                unset($newXml[$index]['installcondition']);
            }
        }
        if (count($newXml) == 1) {
            $newXml = $newXml[0];
        }
        $this->_parent->rawrelease = $newXml;
    }
}