<?php
/**
 * PEAR_PackageFile_v2, package.xml version 2.0
 *
 * PHP versions 5
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
    public $rootAttributes = array(
                                 'version' => '2.1',
                                 'xmlns' => 'http://pear.php.net/dtd/package-2.1',
                                 'xmlns:tasks' => 'http://pear.php.net/dtd/tasks-1.0',
                                 'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                                 'xsi:schemaLocation' => 'http://pear.php.net/dtd/tasks-1.0
    http://pear.php.net/dtd/tasks-1.0.xsd
    http://pear.php.net/dtd/package-2.1
    http://pear.php.net/dtd/package-2.1.xsd',
                             );
    /**
     * Parsed package information
     * @var array
     * @access private
     */
    protected $_packageInfo = array('attribs' => array());

    /**
     * Set if the XML has been validated against schema
     *
     * @var unknown_type
     */
    private $_schemaValidated = false;

    private $_filelist = array();
    private $_baseinstalldirs = array();
    private $_dirtree = array();

    /**
     * path to package .xml or false if this is an abstract parsed-from-string xml
     * @var string|false
     * @access private
     */
    private $_packageFile = false;

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
     * inside Pyrus or by inputting an array saved from an existing PEAR_PackageFile_v2
     * @param array
     */
    function fromArray($pinfo)
    {
        $this->_schemaValidated = true;
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

    function setBaseInstallDirs(array $list)
    {
        $this->_baseinstalldirs = $list;
    }

    /**
     * @param string full path to file
     * @param string attribute name
     * @param string attribute value
     * @return bool success of operation
     */
    function setFileAttribute($filename, $attr, $value)
    {
        if (!in_array($attr, array('role', 'name', 'baseinstalldir', 'install-as'), true)) {
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
        $validator = new PEAR2_Pyrus_PackageFile_v2_Validator;
        return $validator->validate($this, $state);
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

    function getBaseInstallDir($file)
    {
        $file = dirname($file);
        while ($file !== '.' && $file != '/') {
            if (isset($this->_baseinstalldirs[$file])) {
                return $this->_baseinstalldirs[$file];
            }
            $file = dirname($file);
        }
        if (isset($this->_baseinstalldirs[''])) {
            return $this->_baseinstalldirs[''];
        }
        return false;
    }

    function __get($var)
    {
        switch ($var) {
            case 'bundledpackage' :
                if ($this->getPackageType() !== 'bundle') {
                    return false;
                }
                if (!isset($this->_packageInfo['contents'])) {
                    $this->_packageInfo['contents'] = array();
                }
                if (!isset($this->_packageInfo['contents']['bundledpackage'])) {
                    $this->_packageInfo['contents']['bundledpackage'] = array();
                }
                return new ArrayObject($this->_packageInfo['contents']['bundledpackage'],
                    ArrayObject::ARRAY_AS_PROPS);
            case 'packagefile' :
                return $this->_packageFile;
            case 'filepath' :
                return dirname($this->_packageFile);
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
            case 'packagingcontents' :
                PEAR2_Pyrus_PackageFile_v2Iterator_PackagingIterator::setParent($this);
                return new PEAR2_Pyrus_PackageFile_v2Iterator_PackagingIterator(
                            $this->_filelist);
            case 'installGroup' :
                $rel = $this->getPackageType();
                if ($rel != 'bundle') $rel .= 'release';
                $ret = $this->_packageInfo[$rel];
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
            case 'files' :
                return new PEAR2_Pyrus_PackageFile_v2_Files($this->_filelist,
                    ArrayObject::ARRAY_AS_PROPS);
            case 'maintainer' :
                return new PEAR2_Pyrus_PackageFile_v2_Developer($this->_packageInfo);
            case 'rawdeps' :
                return isset($this->_packageInfo['dependencies']) ?
                    $this->_packageInfo['dependencies'] : false;
            case 'dependencies' :
                if (!isset($this->_packageInfo['dependencies'])) {
                    $this->_packageInfo['dependencies'] = array();
                }
                return new PEAR2_Pyrus_PackageFile_v2_Dependencies(
                    $this->_packageInfo['dependencies'],
                    $this->_packageInfo['dependencies']);
            case 'release' :
                $t = $this->getPackageType();
                if (!$t) {
                    $this->type = 'php';
                    $t = 'phprelease';
                } else {
                    if ($t != 'bundle') {
                        $t .= 'release';
                    }
                }
                return new PEAR2_Pyrus_PackageFile_v2_Release(
                    $this->_packageInfo, $this->_packageInfo[$t], $this->_filelist);
            case 'compatible' :
                return new PEAR2_Pyrus_PackageFile_v2_Compatible($this->_packageInfo);
            case 'schemaOK' :
                return $this->_schemaValidated;
        }
        return $this->tag($var);
    }

    function __set($var, $value)
    {
        if ($var === 'license') {
            if (!is_array($value)) {
                $licensemap =
                    array(
                        'php' => 'http://www.php.net/license',
                        'php license' => 'http://www.php.net/license',
                        'lgpl' => 'http://www.gnu.org/copyleft/lesser.html',
                        'bsd' => 'http://www.opensource.org/licenses/bsd-license.php',
                        'bsd style' => 'http://www.opensource.org/licenses/bsd-license.php',
                        'bsd-style' => 'http://www.opensource.org/licenses/bsd-license.php',
                        'mit' => 'http://www.opensource.org/licenses/mit-license.php',
                        'gpl' => 'http://www.gnu.org/copyleft/gpl.html',
                        'apache' => 'http://www.opensource.org/licenses/apache2.0.php'
                    );
                if (isset($licensemap[strtolower($value)])) {
                    $this->_packageInfo['license'] = array(
                        'attribs' => array('uri' =>
                            $licensemap[strtolower($value)]),
                        '_content' => $value
                        );
                } else {
                    // don't use bogus uri
                    $arr['license'] = $value;
                }
            } else {
                $this->_packageInfo['license'] = $value;
            }
            return;
        }
        if ($var === 'type') {
            if (!is_string($value)) {
                throw new PEAR2_Pyrus_PackageFile_Exception('package.xml type must be a ' .
                'string, was a ' . gettype($value));
            }
            if ($value != 'bundle') {
                $value .= 'release';
            }
            if (in_array($value, $a = array('phprelease', 'extsrcrelease', 'extbinrelease',
                  'zendextsrcrelease', 'zendextbinrelease', 'bundle'))) {
                foreach ($a as $type) {
                    if ($value == $type) {
                        if (!isset($this->_packageInfo[$type])) {
                            $this->_packageInfo[$type] = array();
                        }
                        continue;
                    }
                    if (isset($this->_packageInfo[$type])) {
                        unset($this->_packageInfo[$type]);
                    }
                }
            }
            return;
        }
        if ($var === 'dependencies' && $value === null) {
            $this->_packageInfo['dependencies'] = array();
            return;
        }
        if ($var === 'release' && $value === null) {
            $type = $this->getPackageType();
            if ($type != 'bundle') {
                $type .= 'release';
            }
            $this->_packageInfo[$type] = array();
            return;
        }
        if ($value instanceof ArrayObject) {
            $value = $value->getArrayCopy();
        }
        if ($var === 'release' && $value === null) {
            $rel = $this->getPackageType();
            if ($rel) {
                if (isset($this->_packageInfo[$rel . 'release'])) {
                    $rel .= 'release';
                }
                $this->_packageInfo[$rel] = array();
            }
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
        if (!isset($this->_packageInfo[$name]) && in_array($name, array('version',
                'stability', 'license', 'compatible',
                'providesextension', 'usesrole', 'usestask', 'srcpackage', 'srcuri',
                ), true)) {
            $this->_packageInfo[$name] = array();
        }
        if (!isset($this->_packageInfo[$name])) {
            return false;
        }
        if (is_array($this->_packageInfo[$name])) {
            return new ArrayObject($this->_packageInfo[$name], ArrayObject::ARRAY_AS_PROPS);
        }
        return $this->_packageInfo[$name];
    }

    /**
     * Update the changelog based on the current information
     */
    function updateChangelog()
    {
        $license = $this->license;
        if ($license instanceof ArrayObject) {
            $license = $license->getArrayCopy();
        }
        $info = array(
            'version' => array(
                'release' => $this->version['release'],
                'api' => $this->version['api']
            ),
            'stability' => array(
                'release' => $this->stability['release'],
                'api' => $this->stability['api']
            ),
            'date' => $this->date,
            'license' => $license,
            'notes' => $this->notes,
        );
        
        if (!is_array($this->_packageInfo['changelog'])) {
            $this->_packageInfo['changelog'] = $info;
        } elseif (!isset($this->_packageInfo['changelog'][0])) {
            $this->_packageInfo['changelog'] = array($info, $this->_packageInfo['changelog']);
        } else {
            array_unshift($this->_packageInfo['changelog'], $info);
        }
    }

    function __toString()
    {
        $this->_packageInfo['attribs'] = $this->rootAttributes;
        $arr = $this->toArray();
        return (string) new PEAR2_Pyrus_XMLWriter($arr);
    }

    function toArray($forpackaging = false)
    {
        $this->_packageInfo['contents'] = array(
            'dir' => array(
                'attribs' => array('name' => '/'),
                'file' => array()
            ));
        uksort($this->_filelist, 'strnatcasecmp');
        $a = array_reverse($this->_filelist, 1);
        if ($forpackaging) {
            PEAR2_Pyrus_PackageFile_v2Iterator_PackagingIterator::setParent($this);
            $a = new PEAR2_Pyrus_PackageFile_v2Iterator_PackagingIterator($a);
        }
        $temp = array();
        foreach ($a as $name => $stuff) {
            if ($forpackaging) {
                // map old to new name
                $temp[$stuff['attribs']['name']] = $name;
            }
            // if we are packaging, $name is the new name
            $stuff['attribs']['name'] = $name;
            $this->_packageInfo['contents']['dir']['file'][] = $stuff;
        }
        if (count($this->_packageInfo['contents']['dir']['file']) == 1) {
            $this->_packageInfo['contents']['dir']['file'] =
                $this->_packageInfo['contents']['dir']['file'][0];
        }
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
        if ($forpackaging) {
            // process releases
            $reltag = $this->getPackageType();
            if ($reltag != 'bundle') {
                $reltag .= 'release';
                if (is_array($arr[$reltag])) {
                    if (!isset($arr[$reltag][0])) {
                        $arr[$reltag] = array($arr[$reltag]);
                    }
                    foreach ($arr[$reltag] as $i => $inf) {
                        if (!isset($inf['filelist'])) {
                            continue;
                        }
                        $inf = $inf['filelist'];
                        if (isset($inf['install'])) {
                            if (!isset($inf['install'][0])) {
                                if (isset($temp[$inf['install']['attribs']['name']])) {
                                    $arr[$reltag][$i]['filelist']['install']['attribs']
                                                       ['name'] =
                                        $temp[$inf['install']['attribs']['name']];
                                }
                            } else {
                                foreach ($inf['install'] as $j => $morinf) {
                                    if (isset($temp[$morinf['attribs']['name']])) {
                                        $arr[$reltag][$i]['filelist']['install'][$j]
                                                           ['attribs']['name'] =
                                            $temp[$morinf['attribs']['name']];
                                    }
                                }
                            }
                        }
                        if (isset($inf['ignore'])) {
                            if (!isset($inf['ignore'][0])) {
                                if (isset($temp[$inf['ignore']['attribs']['name']])) {
                                    $arr[$reltag][$i]['filelist']['ignore']
                                                       ['attribs']['name'] =
                                        $temp[$inf['ignore']['attribs']['name']];
                                }
                            } else {
                                foreach ($inf['ignore'] as $j => $morinf) {
                                    if (isset($temp[$morinf['attribs']['name']])) {
                                        $arr[$reltag][$i]['filelist']['ignore'][$j]
                                                           ['attribs']['name'] =
                                            $temp[$morinf['attribs']['name']];
                                    }
                                }
                            }
                        }
                    }
                    if (count($arr[$reltag]) == 1) {
                        $arr[$reltag] = $arr[$reltag][0];
                    }
                }
            }
        }
        if (isset($this->_packageInfo['dependencies'])) {
            if (isset($this->_packageInfo['dependencies']['required'])) {
                $arr['dependencies']['required'] = array();
                foreach (array('php', 'pearinstaller', 'package', 'subpackage',
                            'extension', 'os', 'arch') as $index) {
                    if (!isset($this->_packageInfo['dependencies']['required'][$index])) {
                        continue;
                    }
                    $arr['dependencies']['required'][$index] =
                        $this->_packageInfo['dependencies']['required'][$index];
                }
            }
            if (isset($this->_packageInfo['dependencies']['optional'])) {
                $arr['dependencies']['optional'] = array();
                foreach (array('package', 'subpackage', 'extension') as $index) {
                    if (!isset($this->_packageInfo['dependencies']['optional'][$index])) {
                        continue;
                    }
                    $arr['dependencies']['optional'][$index] =
                        $this->_packageInfo['dependencies']['optional'][$index];
                }
            }
            if (isset($this->_packageInfo['dependencies']['group'])) {
                if (isset($this->_packageInfo['dependencies']['group'][0])) {
                    foreach ($this->_packageInfo['dependencies']['group'] as $i => $g) {
                        $arr['dependencies']['group'][$i] = array();
                        foreach (array('attribs', 'package', 'subpackage', 'extension') as $index) {
                            if (!isset($g[$index])) {
                                continue;
                            }
                            $arr['dependencies']['group'][$i][$index] = $g[$index];
                        }
                    }
                } else {
                    $a = $this->_packageInfo['dependencies']['group'];
                    $arr['dependencies']['group'] = array();
                    foreach (array('attribs', 'package', 'subpackage', 'extension') as $index) {
                        if (!isset($a[$index])) {
                            continue;
                        }
                        $arr['dependencies']['group'][$index] = $a[$index];
                    }
                }
            }
        }
        return array('package' => $arr);
    }
}