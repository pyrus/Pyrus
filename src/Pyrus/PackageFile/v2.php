<?php
/**
 * PEAR_PackageFile_v2, package.xml version 2.1
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
 * File representing a package.xml file version 2.1
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
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
     *
     * For created-from-scratch packagefiles, set some basic information needed.
     * @var array
     * @access private
     */
    protected $packageInfo = array('attribs' => array(
        'version' => '2.1',
        'xmlns' => 'http://pear.php.net/dtd/package-2.1',
        'xmlns:tasks' => 'http://pear.php.net/dtd/tasks-1.0',
        'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
        'xsi:schemaLocation' => 'http://pear.php.net/dtd/tasks-1.0
     http://pear.php.net/dtd/tasks-1.0.xsd
     http://pear.php.net/dtd/package-2.1
     http://pear.php.net/dtd/package-2.1.xsd',
    ),
        'version' => array(
            'release' => '0.1.0',
            'api' => '0.1.0',
        ),
        'stability' => array(
            'release' => 'devel',
            'api' => 'alpha',
        ),
        'license' => array('attribs' => array('uri' => 'http://www.opensource.org/licenses/bsd-license.php'),
            '_content' => 'New BSD License',
        ),
        'dependencies' => array(
            'required' => array(
                'php' => array('min' => '5.2.0'),
                'pearinstaller' => array('min' => '2.0.0a1'),
            ),
        ),
        'phprelease' => array(),
    );

    /**
     * Set if the XML has been validated against schema
     *
     * @var unknown_type
     */
    private $_schemaValidated = false;

    protected $filelist = array();
    protected $baseinstalldirs = array();
    private $_dirtree = array();

    /**
     * Mapping of __get variables to method handlers
     * @var array
     */
    protected $getMap = array(
        'bundledpackage' => 'getBundledPackage',
        'packagefile' => 'getPackageFile',
        'filepath' => 'getFilePath',
        'contents' => 'getContents',
        'installcontents' => 'getInstallContents',
        'packagingcontents' => 'getPackagingContents',
        'installGroup' => 'getInstallGroup',
        'channel' => 'getChannel',
        'state' => 'getState',
        'api-version' => 'getApiVersion',
        'release-version' => 'getReleaseVersion',
        'api-state' => 'getApiState',
        'allmaintainers' => 'getAllMaintainers',
        'releases' => 'getReleases',
        'sourcepackage' => 'getSourcePackage',
        'license' => 'getLicense',
        'files' => 'getFiles',
        'maintainer' => 'getMaintainer',
        'rawdeps' => 'getRawDeps',
        'dependencies' => 'getDependencies',
        'release' => 'getRelease',
        'compatible' => 'getCompatible',
        'schemaOK' => 'getSchemaOK',
        'version' => 'tag',
        'stability' => 'tag',
        'providesextension' => 'tag',
        'usesrole' => 'getUsesRole',
        'usestask' => 'getUsesTask',
        'srcpackage' => 'tag',
        'srcchannel' => 'tag',
        'srcuri' => 'tag',
        'name' => 'tag',
        'summary' => 'tag',
        'description' => 'tag',
        'date' => 'tag',
        'time' => 'tag',
        'notes' => 'tag',
    );

    protected $setMap = array(
        'packagefile' => 'setPackageFile',
        'filepath' => 'setFilePath',
        'contents' => 'setContents',
        'channel' => 'setTag',
        'uri' => 'setTag',
        'state' => 'setState',
        'sourcepackage' => 'setSourcePackage',
        'license' => 'setLicense',
        'version' => 'setVersion',
        'stability' => 'setStability',
        'providesextension' => 'setTag',
        'srcpackage' => 'setTag',
        'srcuri' => 'setTag',
        'name' => 'setTag',
        'summary' => 'setTag',
        'description' => 'setTag',
        'date' => 'setTag',
        'time' => 'setTag',
        'notes' => 'setTag',
        'type' => 'setType',
    );

    protected $rawMap = array(
        'rawdependencies' => 'dependencies',
        'rawlicense' => 'license',
        'rawcontents' => 'contents',
        'rawrelease' => array('setRawRelease'), // array says call this function
        'rawcompatible' => 'compatible',
        'rawstability' => 'stability',
        'rawversion' => 'version',
        'rawusesrole' => 'usesrole',
        'rawusestask' => 'usestask',
    );

    /**
     * path to package.xml or false if this is an abstract parsed-from-string xml
     * @var string|false
     * @access private
     */
    protected $_packageFile = false;

    /**
     * path to archive containing this package file, or false if this is a package.xml
     * or abstract parsed-from-string xml
     * @var string|false
     * @access private
     */
    protected $_archiveFile = false;

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

    function getBundledPackage()
    {
        if ($this->getPackageType() !== 'bundle') {
            return false;
        }
        if (!isset($this->packageInfo['contents'])) {
            $this->packageInfo['contents'] = array();
        }
        if (!isset($this->packageInfo['contents']['bundledpackage'])) {
            $this->packageInfo['contents']['bundledpackage'] = array();
        }
        return new PEAR2_Pyrus_PackageFile_v2_BundledPackage($this, $this->packageInfo['contents']['bundledpackage']);
    }

    function getContents()
    {
        // allows stuff like:
        // foreach ($pf->contents as $file) {
        //     echo $file->name;
        //     $file->installed_as = 'hi';
        // }
        return new PEAR2_Pyrus_PackageFile_v2Iterator_File(
                new PEAR2_Pyrus_PackageFile_v2Iterator_FileAttribsFilter(
                new PEAR2_Pyrus_PackageFile_v2Iterator_FileContents(
                    $this->packageInfo['contents'], 'contents', $this)),
                    RecursiveIteratorIterator::LEAVES_ONLY);
    }

    function getInstallContents()
    {
        PEAR2_Pyrus_PackageFile_v2Iterator_FileInstallationFilter::setParent($this);
        return new PEAR2_Pyrus_PackageFile_v2Iterator_FileInstallationFilter(
                new ArrayIterator(
                    $this->filelist));
    }

    function getPackagingContents()
    {
        PEAR2_Pyrus_PackageFile_v2Iterator_PackagingIterator::setParent($this);
        return new PEAR2_Pyrus_PackageFile_v2Iterator_PackagingIterator(
                    $this->filelist);
    }

    function getPackageFile()
    {
        return $this->_packageFile;
    }

    function getFilePath()
    {
        return dirname($this->_packageFile);
    }

    function getInstallGroup()
    {
        $rel = $this->getPackageType();
        if ($rel != 'bundle') $rel .= 'release';
        $ret = $this->packageInfo[$rel];
        if (!isset($ret[0])) {
            return array($ret);
        }
        return $ret;
    }

    function getChannel()
    {
        if (isset($this->packageInfo['uri'])) {
            return '__uri';
        }
        return $this->tag('channel');
    }

    function getState()
    {
        if (!isset($this->packageInfo['stability']) ||
              !isset($this->packageInfo['stability']['release'])) {
            return false;
        }
        return $this->packageInfo['stability']['release'];
    }

    function getApiVersion()
    {
        if (!isset($this->packageInfo['version']) ||
              !isset($this->packageInfo['version']['api'])) {
            return false;
        }
        return $this->packageInfo['version']['api'];
    }

    function getReleaseVersion()
    {
        if (!isset($this->packageInfo['version']) ||
              !isset($this->packageInfo['version']['release'])) {
            return false;
        }
        return $this->packageInfo['version']['release'];
    }

    function getApiState()
    {
        if (!isset($this->packageInfo['stability']) ||
              !isset($this->packageInfo['stability']['api'])) {
            return false;
        }
        return $this->packageInfo['stability']['api'];
    }

    function getAllMaintainers()
    {
        $leads = (array) $this->tag('lead');
        if ($leads && !isset($leads[0])) {
            $leads = array($leads);
        }
        $developers = (array) $this->tag('developer');
        if ($developers && !isset($developers[0])) {
            $developers = array($developers);
        }
        $helpers = (array) $this->tag('helper');
        if ($helpers && !isset($helpers[0])) {
            $helpers = array($helpers);
        }
        $contributors = (array) $this->tag('contributor');
        if ($contributors && !isset($contributors[0])) {
            $contributors = array($contributors);
        }
        return array(
            'lead' => $leads,
            'developer' => $developers,
            'helper' => $helpers,
            'contributor' => $contributors
        );
    }

    function getReleases()
    {
        $type = $this->getPackageType();
        if ($type != 'bundle') {
            $type .= 'release';
        }
        if ($type && isset($this->packageInfo[$type])) {
            return $this->packageInfo[$type];
        }
        return false;
    }

    function getSourcePackage()
    {
        if (isset($this->packageInfo['extbinrelease']) ||
              isset($this->packageInfo['zendextbinrelease'])) {
            return array('channel' => $this->packageInfo['srcchannel'],
                         'package' => $this->packageInfo['srcpackage']);
        }
        return false;
    }

    function getLicense()
    {
        if (!isset($this->packageInfo['license'])) {
            $this->packageInfo['license'] = array();
        }
        return new PEAR2_Pyrus_PackageFile_v2_License($this, $this->packageInfo['license']);
    }

    function getFiles()
    {
        return new PEAR2_Pyrus_PackageFile_v2_Files($this->filelist);
    }

    function getMaintainer()
    {
        return new PEAR2_Pyrus_PackageFile_v2_Developer($this->packageInfo);
    }

    function getRawDeps()
    {
        return isset($this->packageInfo['dependencies']) ?
            $this->packageInfo['dependencies'] : false;
    }

    function getDependencies()
    {
        if (!isset($this->packageInfo['dependencies'])) {
            $this->packageInfo['dependencies'] = array();
        }
        return new PEAR2_Pyrus_PackageFile_v2_Dependencies(
            $this,
            $this->packageInfo['dependencies']);
    }

    function getRelease()
    {
        $t = $this->getPackageType();
        if (!$t) {
            $this->type = 'php';
            $t = 'phprelease';
        } else {
            if ($t != 'bundle') {
                $t .= 'release';
            }
        }
        if (!isset($this->packageInfo[$t]) || !is_array($this->packageInfo[$t])) {
            $this->packageInfo[$t] = array();
        }
        return new PEAR2_Pyrus_PackageFile_v2_Release(
            $this, $this->packageInfo[$t], $this->filelist);
    }

    function getCompatible()
    {
        if (!isset($this->packageInfo['compatible'])) {
            $this->packageInfo['compatible'] = array();
        }
        return new PEAR2_Pyrus_PackageFile_v2_Compatible(
            $this,
            $this->packageInfo['compatible']);
    }

    function getSchemaOK()
    {
        return $this->_schemaValidated;
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
        $this->packageInfo = $pinfo['package'];
    }

    function hasFile($file)
    {
        return isset($this->filelist[$file]);
    }

    function getFile($file)
    {
        return $this->filelist[$file];
    }

    function setFilelist(array $list)
    {
        $this->filelist = $list;
    }

    function setBaseInstallDirs(array $list)
    {
        $this->baseinstalldirs = $list;
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
            // check to see if this is a task
            if ($this->isValidTask($attr)) {
                if (!isset($this->filelist[$filename][$attr])) {
                    $this->filelist[$filename][$attr] = $value;
                } else {
                    if (!isset($this->filelist[$filename][$attr][0])) {
                        $this->filelist[$filename][$attr] = array($this->filelist[$filename][$attr]);
                    }
                    $this->filelist[$filename][$attr][] = $value;
                }
                return;
            }
            throw new PEAR2_Pyrus_PackageFile_Exception(
                'Cannot set invalid attribute ' . $attr . ' for file ' . $filename);
        }
        if (!isset($this->filelist[$filename])) {
            throw new PEAR2_Pyrus_PackageFile_Exception(
                'Cannot set attribute ' . $attr . ' for non-existent file ' . $filename);
        }
        if ($attr == 'name') {
            throw new PEAR2_Pyrus_PackageFile_Exception('Cannot change name of file ' .
                $filename);
        }
        $this->filelist[$filename]['attribs'][$attr] = $value;
    }

    function isValidTask($name)
    {
        $tasksns = $this->getTasksNs();
        if (strlen($name) <= strlen($tasksns) + 1) {
            return false;
        }
        if (substr($name, 0, strlen($tasksns)) === $tasksns) {
            if ($name[strlen($tasksns)] == ':') {
                return true;
            }
        }
        return false;
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
     *
     * This function should only be called when the package has a recommended
     * version tag in a package or subpackage dependency on the package
     * represented by $pf, as no check is done to see whether $this
     * depends on $pf
     * @param PEAR2_Pyrus_Package_Base|PEAR2_Pyrus_PackageFile_v2
     * @return boolean
     */
    function isCompatible($pf)
    {
        if (!isset($this->packageInfo['compatible'])) {
            return false;
        }
        if (!isset($this->packageInfo['channel'])) {
            return false;
        }
        $me = $pf->version['release'];
        $found = false;
        foreach ($this->compatible as $info) {
            if (strtolower($info->name) == strtolower($pf->name)) {
                if ($info->channel == $pf->channel) {
                    $found = true;
                    break;
                }
            }
        }
        if (!$found) {
            return false;
        }
        if (isset($info->exclude)) {
            foreach ($info->exclude as $exclude) {
                if (version_compare($me, $exclude, '==')) {
                    return false;
                }
            }
        }
        if (version_compare($me, $info->min, '>=') && version_compare($me, $info->max, '<=')) {
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
        foreach (array('required', 'optional', 'group') as $type) {
            if ($type === 'group') {
                foreach ($this->dependencies['group'] as $group) {
                    foreach ($group->subpackage as $dep) {
                        if (strtolower($dep->name) == strtolower($p->name)) {
                            if (isset($dep->channel)) {
                                if ($dep->channel == $p->channel) {
                                    return true;
                                }
                            } else {
                                if ($dep->uri == $p->uri) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
            foreach ($this->dependencies[$type]->subpackage as $dep) {
                if (strtolower($dep->name) == strtolower($p->name)) {
                    if (isset($dep->channel)) {
                        if ($dep->channel == $p->channel) {
                            return true;
                        }
                    } else {
                        if ($dep->uri == $p->uri) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Returns true if any dependency, optional or required, exists on the package specified
     */
    function dependsOn($package, $channel, $uri = false)
    {
        $deps = $this->dependencies;
        foreach (array('package', 'subpackage') as $type) {
            foreach (array('required', 'optional') as $needed) {
                foreach ($deps[$needed]->$type as $dep) {
                    if (strtolower($dep->name) == strtolower($package)) {
                        if (isset($dep->channel)) {
                            if ($dep->channel == strtolower($channel)) {
                                return true;
                            }
                        } else {
                            if ($dep->uri === $uri) {
                                return true;
                            }
                        }
                    }
                }
            }
            foreach ($deps['group'] as $group) {
                foreach ($group->$type as $dep) {
                    if (strtolower($dep->name) == strtolower($package)) {
                        if (isset($dep->channel)) {
                            if (strtolower($dep->channel) == strtolower($channel)) {
                                return true;
                            }
                        } else {
                            if ($dep->uri === $uri) {
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
     * @return php|extsrc|extbin|zendextsrc|zendextbin|bundle|false
     */
    function getPackageType()
    {
        if (isset($this->packageInfo['phprelease'])) {
            return 'php';
        }
        if (isset($this->packageInfo['extsrcrelease'])) {
            return 'extsrc';
        }
        if (isset($this->packageInfo['extbinrelease'])) {
            return 'extbin';
        }
        if (isset($this->packageInfo['zendextsrcrelease'])) {
            return 'zendextsrc';
        }
        if (isset($this->packageInfo['zendextbinrelease'])) {
            return 'zendextbin';
        }
        if (isset($this->packageInfo['bundle'])) {
            return 'bundle';
        }
        return false;
    }

    function hasDeps()
    {
        return isset($this->packageInfo['dependencies']);
    }

    function getPackagexmlVersion()
    {
        if (isset($this->packageInfo['zendextsrcrelease'])) {
            return '2.1';
        }
        if (isset($this->packageInfo['zendextbinrelease'])) {
            return '2.1';
        }
        return '2.0';
    }

    /**
     * validate dependencies against the local registry, packages to be installed,
     * and environment (php version, OS, architecture, enabled extensions)
     *
     * @param array $toInstall an array of PEAR2_Pyrus_Package objects
     * @param PEAR2_MultiErrors $errs
     */
    function validateDependencies(array $toInstall, PEAR2_MultiErrors $errs)
    {
        $dep = new PEAR2_Pyrus_Dependency_Validator($this->packageInfo['name'],
            PEAR2_Pyrus_Validate::DOWNLOADING, $errs);
        $dep->validatePhpDependency($this->dependencies['required']->php);
        $dep->validatePearinstallerDependency($this->dependencies['required']->pearinstaller);
        foreach (array('required', 'optional') as $required) {
            foreach ($this->dependencies[$required]->package as $d) {
                $dep->validatePackageDependency($d->getInfo(), $required == 'required', $toInstall);
            }
            foreach ($this->dependencies[$required]->subpackage as $d) {
                $dep->validateSubpackageDependency($d->getInfo(), $required == 'required', $toInstall);
            }
            foreach ($this->dependencies[$required]->extension as $d) {
                $dep->validateExtensionDependency($d->getInfo(), $required == 'required');
            }
        }
        foreach ($this->dependencies['required']->arch as $d) {
            $dep->validateArchDependency($d);
        }
        foreach ($this->dependencies['required']->os as $d) {
            $dep->validateOsDependency($d);
        }
    }

    function validate($state = PEAR2_Pyrus_Validate::NORMAL)
    {
        if (!isset($this->packageInfo) || !is_array($this->packageInfo)) {
            return false;
        }
        $validator = new PEAR2_Pyrus_PackageFile_v2_Validator;
        return $validator->validate($this, $state);
    }

    function getTasksNs()
    {
        if (!isset($this->_tasksNs)) {
            if (isset($this->packageInfo['attribs'])) {
                foreach ($this->packageInfo['attribs'] as $name => $value) {
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
            if (isset($this->baseinstalldirs[$file])) {
                return $this->baseinstalldirs[$file];
            }
            $file = dirname($file);
        }
        if (isset($this->baseinstalldirs[''])) {
            return $this->baseinstalldirs[''];
        }
        return false;
    }

    function __get($var)
    {
        if (isset($this->getMap[$var])) {
            return $this->{$this->getMap[$var]}($var);
        }
        return $this->tag($var);
    }

    function setTag($var, $value)
    {
        if ($var === 'uri') {
            if (isset($this->packageInfo['channel'])) {
                unset($this->packageInfo['channel']);
            }
        }
        if ($var === 'channel') {
            if (isset($this->packageInfo['uri'])) {
                unset($this->packageInfo['uri']);
            }
        }
        $this->packageInfo[$var] = $value;
    }

    function setLicense($var, $value)
    {
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
                $this->packageInfo['license'] = array(
                    'attribs' => array('uri' =>
                        $licensemap[strtolower($value)]),
                    '_content' => $value
                    );
            } else {
                // don't use bogus uri
                $arr['license'] = (string) $value;
            }
        } else {
            $this->packageInfo['license'] = $value;
        }
    }

    function setType($var, $value)
    {
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
                $this->packageInfo['license'] = array(
                    'attribs' => array('uri' =>
                        $licensemap[strtolower($value)]),
                    '_content' => $value
                    );
            } else {
                // don't use bogus uri
                $arr['license'] = (string) $value;
            }
        } else {
            $this->packageInfo['license'] = $value;
        }
    }

    function setRawRelease($var, $value)
    {
        $t = $this->getPackageType();
        if ($t != 'bundle') {
            $t .= 'release';
        }
        $this->packageInfo[$t] = $value;
    }

    function setPackagerVersion($var, $value)
    {
        $this->packageInfo['attribs']['packagerversion'] = $value;
    }

    function __set($var, $value)
    {
        if ($var === 'dependencies' && $value === null) {
            $this->packageInfo['dependencies'] = array();
            return;
        }
        if ($var === 'release' && $value === null) {
            $rel = $this->getPackageType();
            if ($rel) {
                if (isset($this->packageInfo[$rel . 'release'])) {
                    $rel .= 'release';
                }
                $this->packageInfo[$rel] = array();
            }
            return;
        }
        if (isset($this->setMap[$var])) {
            return $this->{$this->setMap[$var]}($var, $value);
        }
        if (isset($this->rawMap[$var])) {
            $actual = $this->rawMap[$var];
            if (is_array($actual)) {
                $actual = $actual[0];
                return $this->$actual($var, $value);
            }
            $this->packageInfo[$actual] = $value;
            return;
        }
        throw new PEAR2_Pyrus_PackageFile_Exception('Cannot set ' . $var . ' directly');
    }

    /**
     * Return the contents of a tag
     * @param string $name
     */
    protected function tag($name)
    {
        if (!isset($this->packageInfo[$name]) && in_array($name, array('version',
                'stability',
                'providesextension', 'usesrole', 'usestask', 'srcpackage', 'srcuri',
                ), true)) {
            $this->packageInfo[$name] = array();
        }
        switch ($name) {
            case 'stability' :
            case 'version' :
                if (!isset($this->packageInfo[$name])) {
                    $this->packageInfo[$name] = array();
                }
                $info = $this->packageInfo[$name];
                if (!isset($info['release'])) {
                    $info['release'] = null;
                }
                if (!isset($info['api'])) {
                    $info['api'] = null;
                }
                return new PEAR2_Pyrus_PackageFile_v2_SimpleProperty(
                                $this, $info, $name);
        }
        if (!isset($this->packageInfo[$name])) {
            return false;
        }
        return $this->packageInfo[$name];
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

        if (!is_array($this->packageInfo['changelog'])) {
            $this->packageInfo['changelog'] = $info;
        } elseif (!isset($this->packageInfo['changelog'][0])) {
            $this->packageInfo['changelog'] = array($info, $this->packageInfo['changelog']);
        } else {
            array_unshift($this->packageInfo['changelog'], $info);
        }
    }

    function __toString()
    {
        $this->packageInfo['attribs'] = $this->rootAttributes;
        $this->packageInfo['date'] = date('Y-m-d');
        $this->packageInfo['time'] = date('H:i:s');
        $arr = $this->toArray();
        return (string) new PEAR2_Pyrus_XMLWriter($arr);
    }

    function toArray($forpackaging = false)
    {
        $this->packageInfo['contents'] = array(
            'dir' => array(
                'attribs' => array('name' => '/'),
                'file' => array()
            ));
        uksort($this->filelist, 'strnatcasecmp');
        $a = array_reverse($this->filelist, 1);
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
            $this->packageInfo['contents']['dir']['file'][] = $stuff;
        }
        if (count($this->packageInfo['contents']['dir']['file']) == 1) {
            $this->packageInfo['contents']['dir']['file'] =
                $this->packageInfo['contents']['dir']['file'][0];
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
            if (!isset($this->packageInfo[$index])) {
                continue;
            }
            $arr[$index] = $this->packageInfo[$index];
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
        $reltag = $this->getPackageType();
        if ($reltag != 'bundle') {
            $reltag .= 'release';
            $sortInstallAs = function ($a, $b)
                {
                    return strnatcasecmp($a['attribs']['name'], $b['attribs']['name']);
                };
            if (!is_array($arr[$reltag])) {
                // do nothing
            } elseif (!isset($arr[$reltag][0])) {
                if (isset($arr[$reltag]['install']) && isset($arr[$reltag]['install'][0])) {
                    usort($arr[$reltag]['install'], $sortInstallAs);
                }
                if (isset($arr[$reltag]['ignore']) && isset($arr[$reltag]['ignore'][0])) {
                    usort($arr[$reltag]['ignore'], $sortInstallAs);
                }
            } else {
                foreach ($arr[$reltag] as $i => &$contents) {
                    if (isset($contents['install']) && isset($contents['install'][0])) {
                        usort($contents['install'], $sortInstallAs);
                    }
                    if (isset($contents['ignore']) && isset($contents['ignore'][0])) {
                        usort($contents['ignore'], $sortInstallAs);
                    }
                }
            }
        }
        if (isset($this->packageInfo['dependencies'])) {
            if (isset($this->packageInfo['dependencies']['required'])) {
                $arr['dependencies']['required'] = array();
                foreach (array('php', 'pearinstaller', 'package', 'subpackage',
                            'extension', 'os', 'arch') as $index) {
                    if (!isset($this->packageInfo['dependencies']['required'][$index])) {
                        continue;
                    }
                    $arr['dependencies']['required'][$index] =
                        $this->packageInfo['dependencies']['required'][$index];
                }
            }
            if (isset($this->packageInfo['dependencies']['optional'])) {
                $arr['dependencies']['optional'] = array();
                foreach (array('package', 'subpackage', 'extension') as $index) {
                    if (!isset($this->packageInfo['dependencies']['optional'][$index])) {
                        continue;
                    }
                    $arr['dependencies']['optional'][$index] =
                        $this->packageInfo['dependencies']['optional'][$index];
                }
            }
            if (isset($this->packageInfo['dependencies']['group'])) {
                if (isset($this->packageInfo['dependencies']['group'][0])) {
                    foreach ($this->packageInfo['dependencies']['group'] as $i => $g) {
                        $arr['dependencies']['group'][$i] = array();
                        foreach (array('attribs', 'package', 'subpackage', 'extension') as $index) {
                            if (!isset($g[$index])) {
                                continue;
                            }
                            $arr['dependencies']['group'][$i][$index] = $g[$index];
                        }
                    }
                } else {
                    $a = $this->packageInfo['dependencies']['group'];
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
