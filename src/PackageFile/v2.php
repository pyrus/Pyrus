<?php
/**
 * PEAR_PackageFile_v2, package.xml version 2.0
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   pear
 * @package    PEAR
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  1997-2006 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: v2.php,v 1.1 2006/12/28 20:42:32 cellog Exp $
 * @link       http://pear.php.net/package/PEAR
 * @since      File available since Release 1.4.0a1
 */
/**
 * @category   pear
 * @package    PEAR
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  1997-2006 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PEAR
 * @since      Class available since Release 1.4.0a1
 */
class PEAR2_Pyrus_PackageFile_v2
{

    /**
     * Parsed package information
     * @var array
     * @access private
     */
    var $_packageInfo = array();
    
    private $_filelist = array();
    private $_dirtree = array();

    /**
     * path to package .xml or false if this is an abstract parsed-from-string xml
     * @var string|false
     * @access private
     */
    var $_packageFile;

    /**
     * Optional Dependency group requested for installation
     * @var string
     * @access private
     */
    var $_requestedGroup = false;

    /**
     * Namespace prefix used for tasks in this package.xml - use tasks: whenever possible
     */
    var $_tasksNs;

    function setPackagefile($file, $archive = false)
    {
        $this->_packageFile = $file;
        $this->_archiveFile = $archive ? $archive : $file;
    }

    function getPackageFile()
    {
        return $this->_packageFile;
    }

    function getArchiveFile()
    {
        return $this->_archiveFile;
    }

    /**
     * Directly set the array that defines this packagefile
     *
     * WARNING: no validation.  This should only be performed by internal methods
     * inside PEAR or by inputting an array saved from an existing PEAR_PackageFile_v2
     * @param array
     */
    function fromArray($pinfo)
    {
        $this->_packageInfo = $pinfo['package'];
    }

    function hasFile($file)
    {
        return isset($this->_filelist[$file]);
    }

    function getFile($file)
    {
        return $this->_filelist[$file];
    }

    function setFilelist(array $list)
    {
        $this->_filelist = $list;
    }

    /**
     * @param string full path to file
     * @param string attribute name
     * @param string attribute value
     * @return bool success of operation
     */
    function setFileAttribute($filename, $attr, $value)
    {
        if (!in_array($attr, array('role', 'name', 'baseinstalldir'), true)) {
            throw new PEAR2_Pyrus_PackageFile_Exception(
                'Cannot set invalid attribute ' . $attr . ' for file ' . $filename);
        }
        if (!isset($this->_filelist[$filename])) {
            throw new PEAR2_Pyrus_PackageFile_Exception(
                'Cannot set attribute ' . $attr . ' for non-existent file ' . $filename);     
        }
        if ($attr == 'name') {
            throw new PEAR2_Pyrus_PackageFile_Exception('Cannot change name of file ' .
                $filename);
        }
        $this->_filelist[$filename]['attribs'][$attr] = $value;
    }

    /**
     * Used by uninstallation to set directory locations to erase
     * @param string $path
     */
    function setDirtree($path)
    {
        $this->_dirtree[$path] = true;
    }

    function getDirtree()
    {
        return $this->_dirtree;
    }

    /**
     * Determines whether this package claims it is compatible with the version of
     * the package that has a recommended version dependency
     * @param PEAR_PackageFile_v2|PEAR_PackageFile_v1|PEAR_Downloader_Package
     * @return boolean
     */
    function isCompatible($pf)
    {
        if (!isset($this->_packageInfo['compatible'])) {
            return false;
        }
        if (!isset($this->_packageInfo['channel'])) {
            return false;
        }
        $me = $pf->version['release'];
        $compatible = $this->_packageInfo['compatible'];
        if (!isset($compatible[0])) {
            $compatible = array($compatible);
        }
        $found = false;
        foreach ($compatible as $info) {
            if (strtolower($info['name']) == strtolower($pf->package)) {
                if (strtolower($info['channel']) == strtolower($pf->channel)) {
                    $found = true;
                    break;
                }
            }
        }
        if (!$found) {
            return false;
        }
        if (isset($info['exclude'])) {
            if (!isset($info['exclude'][0])) {
                $info['exclude'] = array($info['exclude']);
            }
            foreach ($info['exclude'] as $exclude) {
                if (version_compare($me, $exclude, '==')) {
                    return false;
                }
            }
        }
        if (version_compare($me, $info['min'], '>=') && version_compare($me, $info['max'], '<=')) {
            return true;
        }
        return false;
    }

    function isSubpackageOf(PEAR2_Pyrus_PackageFile_v2 $p)
    {
        return $p->isSubpackage($this);
    }

    /**
     * Determines whether the passed in package is a subpackage of this package.
     *
     * No version checking is done, only name verification.
     * @return bool
     */
    function isSubpackage(PEAR2_Pyrus_PackageFile_v2 $p)
    {
        $sub = array();
        if (isset($this->_packageInfo['dependencies']['required']['subpackage'])) {
            $sub = $this->_packageInfo['dependencies']['required']['subpackage'];
            if (!isset($sub[0])) {
                $sub = array($sub);
            }
        }
        if (isset($this->_packageInfo['dependencies']['optional']['subpackage'])) {
            $sub1 = $this->_packageInfo['dependencies']['optional']['subpackage'];
            if (!isset($sub1[0])) {
                $sub1 = array($sub1);
            }
            $sub = array_merge($sub, $sub1);
        }
        if (isset($this->_packageInfo['dependencies']['group'])) {
            $group = $this->_packageInfo['dependencies']['group'];
            if (!isset($group[0])) {
                $group = array($group);
            }
            foreach ($group as $deps) {
                if (isset($deps['subpackage'])) {
                    $sub2 = $deps['subpackage'];
                    if (!isset($sub2[0])) {
                        $sub2 = array($sub2);
                    }
                    $sub = array_merge($sub, $sub2);
                }
            }
        }
        foreach ($sub as $dep) {
            if (strtolower($dep['name']) == strtolower($p->package)) {
                if (isset($dep['channel'])) {
                    if (strtolower($dep['channel']) == strtolower($p->channel)) {
                        return true;
                    }
                } else {
                    if ($dep['uri'] == $p->uri) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    function dependsOn($package, $channel)
    {
        if (!($deps = $this->dependencies)) {
            return false;
        }
        foreach (array('package', 'subpackage') as $type) {
            foreach (array('required', 'optional') as $needed) {
                if (isset($deps[$needed][$type])) {
                    if (!isset($deps[$needed][$type][0])) {
                        $deps[$needed][$type] = array($deps[$needed][$type]);
                    }
                    foreach ($deps[$needed][$type] as $dep) {
                        $depchannel = isset($dep['channel']) ? $dep['channel'] : '__uri';
                        if (strtolower($dep['name']) == strtolower($package) &&
                              $depchannel == $channel) {
                            return true;
                        }  
                    }
                }
            }
            if (isset($deps['group'])) {
                if (!isset($deps['group'][0])) {
                    $dep['group'] = array($deps['group']);
                }
                foreach ($deps['group'] as $group) {
                    if (isset($group[$type])) {
                        if (!is_array($group[$type])) {
                            $group[$type] = array($group[$type]);
                        }
                        foreach ($group[$type] as $dep) {
                            $depchannel = isset($dep['channel']) ? $dep['channel'] : '__uri';
                            if (strtolower($dep['name']) == strtolower($package) &&
                                  $depchannel == $channel) {
                                return true;
                            }  
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get the contents of a dependency group
     * @param string
     * @return array|false
     */
    function getDependencyGroup($name)
    {
        $name = strtolower($name);
        if (!isset($this->_packageInfo['dependencies']['group'])) {
            return false;
        }
        $groups = $this->_packageInfo['dependencies']['group'];
        if (!isset($groups[0])) {
            $groups = array($groups);
        }
        foreach ($groups as $group) {
            if (strtolower($group['attribs']['name']) == $name) {
                return $group;
            }
        }
        return false;
    }

    /**
     * @return php|extsrc|extbin|zendextsrc|zendextbin|bundle|false
     */
    function getPackageType()
    {
        if (isset($this->_packageInfo['phprelease'])) {
            return 'php';
        }
        if (isset($this->_packageInfo['extsrcrelease'])) {
            return 'extsrc';
        }
        if (isset($this->_packageInfo['extbinrelease'])) {
            return 'extbin';
        }
        if (isset($this->_packageInfo['zendextsrcrelease'])) {
            return 'zendextsrc';
        }
        if (isset($this->_packageInfo['zendextbinrelease'])) {
            return 'zendextbin';
        }
        if (isset($this->_packageInfo['bundle'])) {
            return 'bundle';
        }
        return false;
    }

    function hasDeps()
    {
        return isset($this->_packageInfo['dependencies']);
    }

    function getPackagexmlVersion()
    {
        if (isset($this->_packageInfo['zendextsrcrelease'])) {
            return '2.1';
        }
        if (isset($this->_packageInfo['zendextbinrelease'])) {
            return '2.1';
        }
        return '2.0';
    }

    function validate($state = PEAR2_Pyrus_Validate::NORMAL)
    {
        if (!isset($this->_packageInfo) || !is_array($this->_packageInfo)) {
            return false;
        }
        if (!isset($this->_v2Validator) ||
              ($this->_v2Validator instanceof PEAR2_Pyrus_PackageFile_v2_Validator)) {
            $this->_v2Validator = new PEAR2_Pyrus_PackageFile_v2_Validator;
        }
        if (isset($this->_packageInfo['xsdversion'])) {
            unset($this->_packageInfo['xsdversion']);
        }
        return $this->_v2Validator->validate($this, $state);
    }

    function getTasksNs()
    {
        if (!isset($this->_tasksNs)) {
            if (isset($this->_packageInfo['attribs'])) {
                foreach ($this->_packageInfo['attribs'] as $name => $value) {
                    if ($value == 'http://pear.php.net/dtd/tasks-1.0') {
                        $this->_tasksNs = str_replace('xmlns:', '', $name);
                        break;
                    }
                }
            }
        }
        return $this->_tasksNs;
    }

    /**
     * Determine whether a task name is a valid task.  Custom tasks may be defined
     * using subdirectories by putting a "-" in the name, as in <tasks:mycustom-task>
     *
     * Note that this method will auto-load the task class file and test for the existence
     * of the name with "-" replaced by "_" as in PEAR/Task/mycustom/task.php makes class
     * PEAR_Task_mycustom_task
     * @param string
     * @return boolean
     */
    function getTask($task)
    {
        $this->getTasksNs();
        // transform all '-' to '/' and 'tasks:' to '' so tasks:replace becomes replace
        $task = str_replace(array($this->_tasksNs . ':', '-'), array('', ' '), $task);
        $task = str_replace(' ', '/', ucwords($task));
        $test = str_replace('/', '_', $task);
        if (class_exists("PEAR2_Pyrus_Task_$test", true)) {
            return "PEAR2_Pyrus_Task_$test";
        }
        return false;
    }

    function __get($var)
    {
        switch ($var) {
            case 'contents' :
                // allows stuff like:
                // foreach ($pf->contents as $file) {
                //     echo $file->name;
                //     $file->installed_as = 'hi';
                // }
                return new PEAR2_Pyrus_PackageFile_v2Iterator_File(
                        new PEAR2_Pyrus_PackageFile_v2Iterator_FileAttribsFilter(
                        new PEAR2_Pyrus_PackageFile_v2Iterator_FileContents(
                            $this->_packageInfo['contents'], 'contents', $this)),
                            RecursiveIteratorIterator::LEAVES_ONLY);
            case 'installcontents' :
                PEAR2_Pyrus_PackageFile_v2Iterator_FileInstallationFilter::setParent($this);
                return new PEAR2_Pyrus_PackageFile_v2Iterator_File(
                        new PEAR2_Pyrus_PackageFile_v2Iterator_FileInstallationFilter(
                        new PEAR2_Pyrus_PackageFile_v2Iterator_FileContents(
                            $this->_packageInfo['contents'], 'contents', $this)),
                            RecursiveIteratorIterator::LEAVES_ONLY);
            case 'installGroup' :
                $ret = $this->getReleases();
                if (!isset($ret[0])) {
                    return array($ret);
                }
                return $ret;
            case 'channel' :
                if (isset($this->_packageInfo['uri'])) {
                    return '__uri';
                }
                break;
            case 'state' :
                if (!isset($this->_packageInfo['stability']) ||
                      !isset($this->_packageInfo['stability']['release'])) {
                    return false;
                }
                return $this->_packageInfo['stability']['release'];
            case 'api-version' :
                if (!isset($this->_packageInfo['version']) ||
                      !isset($this->_packageInfo['version']['api'])) {
                    return false;
                }
                return $this->_packageInfo['version']['api'];
            case 'api-state' :
                if (!isset($this->_packageInfo['stability']) ||
                      !isset($this->_packageInfo['stability']['api'])) {
                    return false;
                }
                return $this->_packageInfo['stability']['api'];
            case 'allmaintainers' :
                $leads = $this->tag('lead');
                if ($leads && !isset($leads[0])) {
                    $leads = array($leads);
                }
                $developers = $this->tag('developer');
                if ($developers && !isset($developers[0])) {
                    $developers = array($developers);
                }
                $helpers = $this->tag('helper');
                if ($helpers && !isset($helpers[0])) {
                    $helpers = array($helpers);
                }
                $contributors = $this->tag('contributors');
                if ($contributors && !isset($contributors[0])) {
                    $contributors = array($contributors);
                }
                return array(
                    'lead' => $leads,
                    'developer' => $developers,
                    'helper' => $helpers,
                    'contributor' => $contributors
                );
            case 'releases' :
                $type = $this->getPackageType();
                if ($type != 'bundle') {
                    $type .= 'release';
                }
                if ($type && isset($this->_packageInfo[$type])) {
                    return $this->_packageInfo[$type];
                }
                return false;
            case 'sourcepackage' :
                if (isset($this->_packageInfo['extbinrelease']) ||
                      isset($this->_packageInfo['zendextbinrelease'])) {
                    return array('channel' => $this->_packageInfo['srcchannel'],
                                 'package' => $this->_packageInfo['srcpackage']);
                }
                return false;
            case 'file' :
                return new ArrayObject($this->_filelist, ArrayObject::ARRAY_AS_PROPS);
            case 'maintainer' :
                return new PEAR2_Pyrus_PackageFile_v2_Developer($this->_packageInfo);
            case 'compatible' :
                return new PEAR2_Pyrus_PackageFile_v2_Compatible($this->_packageInfo);
        }
        return $this->tag($var);
    }

    function __set($var, $value)
    {
        if ($value instanceof ArrayObject) {
            $value = $value->getArrayCopy();
        }
        if ($var === 'dependencies' && $value === null) {
            $this->_packageInfo['dependencies'] = array();
            return;
        }
        if (in_array($var, array('attribs', 'lead',
                'developer', 'contributor', 'helper', 'version',
                'stability', 'license', 'contents', 'compatible',
                'dependencies', 'providesextension', 'usesrole', 'usestask', 'srcpackage', 'srcuri',
                'phprelease', 'extsrcrelease', 'zendextsrcrelease', 'zendextbinrelease',
                'extbinrelease', 'bundle', 'changelog'), true)) {
            throw new PEAR2_Pyrus_PackageFile_Exception('Cannot set ' . $var . ' directly');
        }
        if (!in_array($var, array('name', 'channel', 'uri', 'extends', 'summary',
                'description', 'date', 'time','notes'), true)) {
            return;
        }
        $this->_packageInfo[$var] = $value;
    }

    /**
     * Return the contents of a tag
     * @param string $name
     */
    protected function tag($name)
    {
        if (!isset($this->_packageInfo[$name])) {
            return false;
        }
        if (isset($this->_packageInfo[$name]['_contents'])) {
            return $this->_packageInfo[$name]['_contents'];
        }
        if (is_array($this->_packageInfo[$name])) {
            return new ArrayObject($this->_packageInfo[$name], ArrayObject::ARRAY_AS_PROPS);
        }
        return $this->_packageInfo[$name];
    }

    function __toString()
    {
        $arr = $this->_packageInfo;
        $tree = $arr['dirtree'];
        $arr['dirtree'] = array('path' => array_keys($arr['dirtree']));
        unset($arr['filelist']);
        $arr['attribs'] = $this->options['rootAttributes'];
        $arr = array('package' => $arr);
        $arr['package']['attribs']['packagerversion'] = '@PEAR-VER@';
        return (string) new PEAR2_Pyrus_XMLWriter($arr);
    }

    function toArray()
    {
        $arr = array();
        foreach (array('attribs', 'name', 'channel', 'uri', 'extends', 'summary',
                'description', 'lead',
                'developer', 'contributor', 'helper', 'date', 'time', 'version',
                'stability', 'license', 'notes', 'contents', 'compatible',
                'dependencies', 'providesextension', 'usesrole', 'usestask', 'srcpackage', 'srcuri',
                'phprelease', 'extsrcrelease', 'zendextsrcrelease', 'zendextbinrelease',
                'extbinrelease', 'bundle', 'changelog') as $index)
        {
            if (!isset($this->_packageInfo[$index])) {
                continue;
            }
            $arr[$index] = $this->_packageInfo[$index];
        }
        $this->_packageInfo['contents'] = array(
            'dir' => array(
                'attribs' => array('name' => '/'),
                'file' => array()
            ));
        uksort($this->_filelist, 'strnatcasecmp');
        foreach (array_reverse($this->_filelist, 1) as $name => $stuff) {
            $this->_packageInfo['contents']['file'][] = $stuff;
        }
        return $this->_packageInfo;
    }
}