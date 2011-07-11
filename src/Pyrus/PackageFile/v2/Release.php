<?php
/**
 * \Pyrus\PackageFile\v2\Release
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
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\PackageFile\v2;
class Release implements \ArrayAccess, \Countable
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

    function __isset($var)
    {
        if (!isset($this->index)) {
            return false;
        }

        return isset($this->info[$var]);
    }

    function __get($var)
    {
        if (!isset($this->index)) {
            return null;
        }

        if ($var === 'configureoption') {
            if (!isset($this->info['configureoption'])) {
                $info = array();
            } else {
                $info = $this->info['configureoption'];
                if (!is_array($info) || !isset($info[0])) {
                    $info = array($info);
                }
            }

            return new Release\ConfigureOption($this, $info);
        }

        if ($var === 'binarypackage') {
            if (!isset($this->info['binarypackage'])) {
                $info = array();
            } else {
                $info = $this->info['binarypackage'];
                if (!is_array($info)) {
                    $info = array($info);
                }
            }

            return new Release\BinaryPackage($this, $info);
        }

        if ($var === 'installconditions') {
            if (!isset($this->info['installcondition'])) {
                $this->info['installcondition'] = array();
            }

            $conditions = new Release\InstallCondition($this, $this->info['installcondition']);
            return $conditions;
        }

        throw new Release\Exception('Unknown variable ' . $var . ', installconditions is the only supported variable');
    }

    function setBinaryPackage($info)
    {
        $this->info['binarypackage'] = $info;
    }

    function setConfigureOption($info)
    {
        $this->info['configureoption'] = $info;
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
                        throw new Release\Exception('Can only set the next highest release index ' .
                                                    count($this) . ', not ' . $var);
                    }

                    $this->info[$var] = array();
                }

                return new Release($this, $this->info[$var], $this->_filelist, $var);
            }

            if ($var !== 0) {
                throw new Release\Exception('Can only set the ' .
                    'next highest release index 0, not ' . $var);
            }

            $this->info[$var] = array();
            return new Release($this, $this->info[$var], $this->_filelist, $var);
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

        if (!isset($this->info['filelist'])) {
            return false;
        }

        if (!isset($this->info['filelist']['install'])) {
            return false;
        }

        return $this->info['filelist']['install'];
    }

    function getIgnore()
    {
        if (!isset($this->index)) {
            return null;
        }

        if (!isset($this->info['filelist'])) {
            return false;
        }

        if (!isset($this->info['filelist']['ignore'])) {
            return false;
        }

        return $this->info['filelist']['ignore'];
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

        if (is_int($var) === false) {
            throw new Release\Exception('Cannot set ' . $var);
        }

        if ($value instanceof Release) {
            $this->info[$var] = array('installcondition' => $value->getInstallCondition(),
                                      'install' => $value->getInstallAs(),
                                      'ignore' => $value->getIgnore());
            $this->save();
            return;
        }

        throw new Release\Exception('Cannot set ' . $var . ' to non-\Pyrus\PackageFile\v2\Release');
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
            throw new Release\Exception('Cannot ignore file ' .
                                        $file . ' without specifying which release section to ignore it in');
        }

        if (isset($this->_filelist[$file])) {
            if (!isset($this->info['filelist'])) {
                $this->info['filelist'] = array();
            }

            if (!isset($this->info['filelist']['ignore'])) {
                $this->info['filelist']['ignore'] = array(array('attribs' => array('name' => $file)));
            } else {
                if (!isset($this->info['filelist']['ignore'][0])) {
                    $this->info['filelist']['ignore'] = array($this->info['filelist']['ignore']);
                }

                $this->info['filelist']['ignore'][] = array('attribs' => array('name' => $file));
            }

            $this->save();
            return;
        }

        throw new Release\Exception('Unknown file ' . $file . ' - add to filelist before ignoring');
    }

    function installAs($file, $newname)
    {
        if (!isset($this->index)) {
            throw new Release\Exception('Cannot install file ' . $file . ' to ' . $newname .
                            ' without specifying which release section to install it in');
        }

        if (isset($this->_filelist[$file])) {
            if (!isset($this->info['filelist'])) {
                $this->info['filelist'] = array();
            }

            if (!isset($this->info['filelist']['install'])) {
                $this->info['filelist']['install'] = array(array('attribs' =>
                    array('name' => $file, 'as' => $newname)));
            } else {
                if (!isset($this->info['filelist']['install'][0])) {
                    $this->info['filelist']['install'] = array($this->info['filelist']['install']);
                }

                $this->info['filelist']['install'][] = array('attribs' =>
                    array('name' => $file, 'as' => $newname));
            }

            $this->save();
            return;
        }

        throw new Release\Exception('Unknown file ' . $file .
            ' - add to filelist before adding install as tag');
    }

    function setInstallCondition(Release\InstallCondition $c)
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
    function save()
    {
        if (isset($this->index)) {
            $this->_parent->setReleaseInfo($this->index, $this->info);
            return $this->_parent->save();
        }

        $newXml = $this->info;
        foreach ($newXml as $index => $info) {
            if (isset($info['filelist'])) {
                if (isset($info['filelist']['ignore']) && count($info['filelist']['ignore']) == 1 && !isset($info['filelist']['ignore']['attribs'])) {
                    $newXml[$index]['filelist']['ignore'] = $newXml[$index]['filelist']['ignore'][0];
                }

                if (isset($info['filelist']['install']) && count($info['filelist']['install']) == 1 && !isset($info['filelist']['install']['attribs'])) {
                    $newXml[$index]['filelist']['install'] = $newXml[$index]['filelist']['install'][0];
                }

                if (array_key_exists('ignore', $info['filelist']) && !count($info['filelist']['ignore'])) {
                    unset($newXml[$index]['filelist']['ignore']);
                }

                if (array_key_exists('install', $info['filelist']) && !count($info['filelist']['install'])) {
                    unset($newXml[$index]['filelist']['install']);
                }
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
                            array_values($newXml[$index]['installcondition']['extension']);
                        $newXml[$index]['installcondition']['extension'] =
                            $newXml[$index]['installcondition']['extension'][0];
                    } elseif (count($newXml[$index]['installcondition']['extension']) == 0) {
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