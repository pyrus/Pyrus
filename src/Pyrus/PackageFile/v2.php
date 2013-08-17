<?php
/**
 * Pyrus\PackageFile\v2, package.xml version 2.1
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
namespace Pyrus\PackageFile;

use ArrayIterator;
use ArrayObject;
use PEAR2\MultiErrors;
use Pyrus\Dependency\Validator as Validator2;
use Pyrus\PackageFile\v2\BundledPackage;
use Pyrus\PackageFile\v2\Compatible;
use Pyrus\PackageFile\v2\Configureoption;
use Pyrus\PackageFile\v2\Dependencies;
use Pyrus\PackageFile\v2\Dependencies\Dep;
use Pyrus\PackageFile\v2\Developer;
use Pyrus\PackageFile\v2\Files;
use Pyrus\PackageFile\v2\License;
use Pyrus\PackageFile\v2\Release;
use Pyrus\PackageFile\v2\SimpleProperty;
use Pyrus\PackageFile\v2\UsesRoleTask;
use Pyrus\PackageFile\v2\Validator;
use Pyrus\PackageFile\v2Iterator\File;
use Pyrus\PackageFile\v2Iterator\FileAttribsFilter;
use Pyrus\PackageFile\v2Iterator\FileContents;
use Pyrus\PackageFile\v2Iterator\FileInstallationFilter;
use Pyrus\PackageFile\v2Iterator\PackagingFilterBase;
use Pyrus\PackageFile\v2Iterator\PackagingIterator;
use Pyrus\PackageFile\v2Iterator\ScriptFileFilterIterator;
use Pyrus\PackageFileInterface;
use Pyrus\Task\Common;
use Pyrus\Validate;
use Pyrus\XMLWriter;
use RecursiveIteratorIterator;

/**
 * File representing a package.xml file version 2.1
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 * 
 * @property string                        $channel           Alias of
 *     {@link getChannel()} when getting, alias of {@link setChannel()} when
 *     setting.
 * @property File                          $contents          Alias of
 *     {@link getContents()} when getting, alias of {@link setContents()} when
 *     setting.
 * @property string                        $date              Alias of
 *     {@link tag()} with 'date' as an argument when getting, alias of
 *     {@link setTag()} with 'date' as the first argument when setting.
 * @property string                        $description       Alias of
 *     {@link tag()} with 'description' as an argument when getting, alias of
 *     {@link setTag() with 'description' as the first argument when setting.
 * @property string                        $filepath          Alias of
 *     {@link getFilePath()} when getting, alias of {@link setFilePath()} when
 *     setting.
 * @property License                       $license           Alias of
 *     {@link getLicense()} when getting, alias of {@link setLicense()} when
 *     setting.
 * @property string                        $name              Alias of
 *     {@link tag()} with 'name' as an argument when getting, alias of
 *     {@link setTag()} with 'name' as the first argument when setting.
 * @property string                   $notes             Alias of
 *     {@link tag()} with 'notes' as an argument when getting, alias of
 *     {@link setTag()} with 'notes' as the first argument when setting.
 * @property string                        $packagefile       Alias of
 *     {@link getPackageFile()} when getting, alias of {@link setPackageFile()}
 *     when setting.
 * @property array                         $providesextension Alias of
 *     {@link tag()} with 'providesextension' as an argument when getting,
 *     alias of {@link setTag()} with 'providesextension' as the first argument
 *     when setting.
 * @property string                        $requestedGroup    Alias of
 *     {@link getRequestedGroup()} when getting, alias of
 *     {@link setRequestedGroup()} when setting.
 * @property array[]                       $sourcepackage     Alias of
 *     {@link getSourcePackage()} when getting, alias of
 *     {@link setSourcePackage()} when setting.
 * @property array                         $srcpackage        Alias of
 *     {@link tag()} with 'srcpackage' as an argument when getting, alias of
 *     {@link setTag()} with 'srcpackage' as an argument when setting.
 * @property string                        $srcchannel        Alias of
 *     {@link tag()} with 'srcchannel' as an argument when getting, alias of
 *     {@link setTag()} with 'srcchannel' as an argument when setting.
 * @property string                        $srcuri            Alias of
 *     {@link tag()} with 'srcuri' as an argument when getting, alias of
 *     {@link setTag()} with 'srcuri' as an argument when setting.
 * @property string                        $stability         Alias of
 *     {@link tag()} with 'stability' as an argument when getting, alias of
 *     {@link setTag()} with 'stability' as an argument when setting.
 * @property string|bool                   $state             Alias of
 *     {@link getState()} when getting, alias of {@link setState()} when
 *     setting.
 * @property string                        $summary           Alias of
 *     {@link tag()} with 'summary' as an argument when getting, alias of
 *     {@link setTag()} with 'summary' as an argument when setting.
 * @property int                           $time              Alias of
 *     {@link tag()} with 'time' as an argument when getting, alias of
 *     {@link setTag()} with 'time' as an argument when setting.
 * @property string                        $type              Alias of
 *     {@link getPackageType()} when getting, alias of {@link setPackageType()}
 *     when setting.
 * @property string                        $version           Alias of
 *     {@link tag()} with 'version' as an argument when getting, alias of
 *     {@link setTag()} with 'version' as an argument when setting.
 * 
 * @property-read array[]                  $allmaintainers    Alias of
 *     {@link getAllMaintainers()}.
 * @property-read string                   $api-version       Alias of
 *     {@link getApiVersion()}.
 * @property-read string                   $api-state         Alias of
 *     {@link getApiState()}.
 * @property-read BundledPackage           $bundledpackage    Alias of
 *     {@link getBundledPackage()}.
 * @property-read Compatible               $compatible        Alias of
 *     {@link getCompatible()}.
 * @property-read Dependencies             $dependencies      Alias of
 *     {@link getDependencies()}.
 * @property-read Files                    $files             Alias of
 *     {@link getFiles()}.
 * @property-read FileInstallationFilter   $installcontents   Alias of
 *     {@link getInstallContents()}.
 * @property-read array                    $installGroup      Alias of
 *     {@link getInstallGroup()}.
 * @property-read Release                  $installrelease    Alias of
 *     {@link getReleaseToInstall()}.
 * @property-read Developer                $maintainer        Alias of
 *     {@link getMaintainer()}.
 * @property-read PackagingIterator        $packagingcontents Alias of
 *     {@link getPackagingContents()}.
 * @property-read array[]                  $rawdeps           Alias of
 *     {@link getRawDeps()}.
 * @property-read Release                  $release           Alias of
 *     {@link getRelease()}.
 * @property-read string                   $release-version   Alias of
 *     {@link getReleaseVersion()}.
 * @property-read array[]                  $releases          Alias of
 *     {@link getReleases()}.
 * @property-read bool                     $schemaOK          Alias of
 *     {@link getSchemaOK()}.
 * @property-read ScriptFileFilterIterator $scriptfiles       Alias of
 *     {@link getScriptFiles()}.
 * @property-read UsesRoleTask             $usesrole          Alias of
 *     {@link getUsesRoleTask()}.
 * @property-read UsesRoleTask             $usestask          Alias of
 *     {@link getUsesRoleTask()}.
 * 
 * @property-write string                  $packagerversion   Alias of
 *     {@link setPackagerVersion()}.
 * @property-write string                  $uri               Alias of
 *     {@link setTag()} with 'uri' as the first argument.
 */
class v2 implements PackageFileInterface
{
    /**
     * @var string[string]
     */
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
     * 
     * @var array
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

    protected $releaseIndex;

    /**
     * Set if the XML has been validated against schema
     *
     * @var bool TRUE if validated, FALSE otherwise.
     */
    private $_schemaValidated = false;

    protected $filelist = array();
    protected $baseinstalldirs = array();
    private $_dirtree = array();

    /**
     * Mapping of __get variables to method handlers
     * 
     * @var string[string]
     */
    protected $getMap = array(
        'channel' => 'getChannel',
        'contents' => 'getContents',
        'date' => 'tag',
        'description' => 'tag',
        'filepath' => 'getFilePath',
        'license' => 'getLicense',
        'name' => 'tag',
        'notes' => 'tag',
        'packagefile' => 'getPackageFile',
        'providesextension' => 'tag',
        'requestedGroup' => 'getRequestedGroup',
        'sourcepackage' => 'getSourcePackage',
        'srcpackage' => 'tag',
        'srcchannel' => 'tag',
        'srcuri' => 'tag',
        'stability' => 'tag',
        'state' => 'getState',
        'summary' => 'tag',
        'time' => 'tag',
        'type' => 'getPackageType',
        'version' => 'tag',

        'allmaintainers' => 'getAllMaintainers',
        'api-version' => 'getApiVersion',
        'api-state' => 'getApiState',
        'bundledpackage' => 'getBundledPackage',
        'compatible' => 'getCompatible',
        'dependencies' => 'getDependencies',
        'files' => 'getFiles',
        'installcontents' => 'getInstallContents',
        'installGroup' => 'getInstallGroup',
        'installrelease' => 'getReleaseToInstall',
        'maintainer' => 'getMaintainer',
        'packagingcontents' => 'getPackagingContents',
        'rawdeps' => 'getRawDeps',
        'release' => 'getRelease',
        'release-version' => 'getReleaseVersion',
        'releases' => 'getReleases',
        'schemaOK' => 'getSchemaOK',
        'scriptfiles' => 'getScriptFiles',
        'usesrole' => 'getUsesRoleTask',
        'usestask' => 'getUsesRoleTask',
    );

    /**
     * Mapping of __set variables to method handlers
     * 
     * @var string[string]
     */
    protected $setMap = array(
        'channel' => 'setTag',
        'contents' => 'setContents',
        'date' => 'setTag',
        'description' => 'setTag',
        'extends' => 'setTag',
        'filepath' => 'setFilePath',
        'license' => 'setLicense',
        'name' => 'setTag',
        'notes' => 'setTag',
        'packagefile' => 'setPackageFile',
        'providesextension' => 'setTag',
        'requestedGroup' => 'setRequestedGroup',
        'sourcepackage' => 'setSourcePackage',
        'srcpackage' => 'setTag',
        'srcuri' => 'setTag',
        'stability' => 'setStability',
        'state' => 'setState',
        'summary' => 'setTag',
        'time' => 'setTag',
        'type' => 'setType',
        'version' => 'setVersion',

        'packagerversion' => 'setPackagerVersion',
        'uri' => 'setTag',
    );

    protected $rawMap = array(
        'rawdependencies' => 'dependencies',
        'rawlicense' => 'license',
        'rawcontents' => 'contents',
        'rawconfigureoption' => array('setRawConfigureoption'), // array says call this function
        'rawrelease' => array('setRawRelease'), // array says call this function
        'rawcompatible' => 'compatible',
        'rawstability' => 'stability',
        'rawversion' => 'version',
        'rawusesrole' => 'usesrole',
        'rawusestask' => 'usestask',
        'rawlead' => 'lead',
        'rawdeveloper' => 'developer',
        'rawcontributor' => 'contributor',
        'rawhelper' => 'helper',
    );

    /**
     * path to package.xml or false if this is an abstract parsed-from-string
     * xml
     * 
     * @var string|false
     */
    protected $_packageFile = false;

    /**
     * path to archive containing this package file, or false if this is a
     * package.xml or abstract parsed-from-string xml.
     * 
     * @var string|false
     */
    protected $_archiveFile = false;

    /**
     * Optional Dependency group requested for installation.
     * 
     * @var string
     */
    protected $requestedGroup = false;

    /**
     * Namespace prefix used for tasks in this package.xml - use "tasks:"
     * whenever possible.
     * 
     * @var string
     */
    public $_tasksNs;

    static protected $packagingFilterPrototype = array();

    function setPackagefile($file, $archive = false)
    {
        $this->_packageFile = $file;
        $this->_archiveFile = $archive ? $archive : $file;
    }

    /**
     * @return boolean FALSE, always.
     */
    function hasConcreteVersion()
    {
        return false;
    }

    function getReleaseToInstall($var, $reset = false)
    {
        if (!$reset && isset($this->releaseIndex)) {
            return $this->release[$this->releaseIndex];
        }

        $errs = new MultiErrors;
        $depchecker = new Validator2(
            array('channel' => $this->channel,
                  'package' => $this->name),
            Validate::INSTALLING, $errs);
        foreach ($this->installGroup as $index => $instance) {
            try {
                if (isset($instance['installconditions'])) {
                    $installconditions = $instance['installconditions'];
                    if (is_array($installconditions)) {
                        foreach ($installconditions as $type => $conditions) {
                            if (!isset($conditions[0])) {
                                $conditions = array($conditions);
                            }

                            foreach ($conditions as $condition) {
                                $condition = new Dep(null, $condition, $type);
                                $ret = $depchecker->{"validate{$type}Dependency"}($condition);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // can't use this release
                continue;
            }

            $this->releaseIndex = $index;
            return $this->release[$index];
        }
    }

    /**
     * @return boolean|BundledPackage
     */
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

        return new BundledPackage($this, $this->packageInfo['contents']['bundledpackage']);
    }

    /**
     * Fetches package content
     *
     * allows stuff like:
     * <code>
     * foreach ($pf->contents as $file) {
     *      echo $file->name;
     *      $file->installed_as = 'hi';
     * }
     * </code>
     * 
     * @return File
     */
    function getContents()
    {
        return new File(
                new FileAttribsFilter(
                new FileContents(
                    $this->packageInfo['contents'], 'contents', $this)),
                    RecursiveIteratorIterator::LEAVES_ONLY);
    }

    /**
     * @return FileInstallationFilter
     */
    function getInstallContents()
    {
        FileInstallationFilter::setParent($this);
        return new FileInstallationFilter(new ArrayIterator($this->filelist));
    }

    function setPackagingFilter($filter)
    {
        if (is_string($filter)) {
            $filter = new $filter(new PackagingIterator($this->filelist));
        }

        if (!($filter instanceof PackagingFilterBase)) {
            throw new Exception('Can only set packaging filter to a child of ' .
                                'Pyrus\PackageFile\v2Iterator\PackagingFilterBase');
        }

        self::$packagingFilterPrototype[$this->channel . '/' . $this->name] = $filter;
    }

    /**
     * @return PackagingIterator
     */
    function getPackagingContents()
    {
        PackagingIterator::setParent($this);
        if (isset(self::$packagingFilterPrototype[$this->channel . '/' . $this->name])) {
            $iterator = new PackagingIterator($this->filelist);
            return self::$packagingFilterPrototype[$this->channel . '/' . $this->name]->getIterator($iterator);
        }

        return new PackagingIterator($this->filelist);
    }

    /**
     * @return ScriptFileFilterIterator
     */
    function getScriptFiles()
    {
        return new ScriptFileFilterIterator($this->filelist, $this);
    }

    /**
     * @return string
     */
    function getPackageFile()
    {
        return $this->_packageFile;
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    function getChannel()
    {
        if (isset($this->packageInfo['uri'])) {
            return '__uri';
        }

        return $this->tag('channel');
    }

    /**
     * @return string|bool The release state, or FALSE if it's not set.
     */
    function getState()
    {
        if (!isset($this->packageInfo['stability']) ||
            !isset($this->packageInfo['stability']['release'])
        ) {
            return false;
        }

        return $this->packageInfo['stability']['release'];
    }

    /**
     * @return string|bool The API version, or FALSE if it's not set.
     */
    function getApiVersion()
    {
        if (!isset($this->packageInfo['version']) ||
            !isset($this->packageInfo['version']['api'])
        ) {
            return false;
        }

        return $this->packageInfo['version']['api'];
    }

    /**
     * @return string|bool The release version, or FALSE if it's not set.
     */
    function getReleaseVersion()
    {
        if (!isset($this->packageInfo['version']) ||
            !isset($this->packageInfo['version']['release'])
        ) {
            return false;
        }

        return $this->packageInfo['version']['release'];
    }

    /**
     * @return string|bool The API state, or FALSE if it's not set.
     */
    function getApiState()
    {
        if (!isset($this->packageInfo['stability']) ||
            !isset($this->packageInfo['stability']['api'])
        ) {
            return false;
        }

        return $this->packageInfo['stability']['api'];
    }

    function getAllMaintainers()
    {
        $ret = array('lead' => array(), 'developer' => array(), 'helper' => array(), 'contributor' => array());
        foreach ($this->maintainer as $maintainer) {
            $ret[$maintainer->role][] = $maintainer;
        }

        return $ret;
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

    /**
     * @return boolean|array
     */
    function getSourcePackage()
    {
        if (isset($this->packageInfo['extbinrelease']) ||
            isset($this->packageInfo['zendextbinrelease'])
        ) {
            return array('channel' => $this->packageInfo['srcchannel'],
                         'package' => $this->packageInfo['srcpackage']);
        }

        return false;
    }

    /**
     * @return License
     */
    function getLicense()
    {
        if (!isset($this->packageInfo['license'])) {
            $this->packageInfo['license'] = array();
        }

        return new License($this, $this->packageInfo['license']);
    }

    /**
     * @return Files
     */
    function getFiles()
    {
        return new Files($this, $this->filelist);
    }

    /**
     * @param string $var
     * 
     * @return UsesRoleTask
     */
    function getUsesRoleTask($var)
    {
        if (!isset($this->packageInfo[$var])) {
            return new UsesRoleTask($this, array(), str_replace('uses', '', $var));
        }

        $info = $this->packageInfo[$var];
        if (count($info) && !isset($info[0])) {
            $info = array($info);
        }

        return new UsesRoleTask($this, $info, str_replace('uses', '', $var));
    }

    /**
     * @return Developer
     */
    function getMaintainer()
    {
        $info = array();
        foreach (array('lead', 'developer', 'contributor', 'helper') as $type) {
            if (isset($this->packageInfo[$type])) {
                $info[$type] = $this->packageInfo[$type];
                if (!isset($info[$type][0])) {
                    $info[$type] = array($info[$type]);
                }
            } else {
                $info[$type] = array();
            }
        }

        return new Developer($this, $info);
    }

    function getRawDeps()
    {
        return isset($this->packageInfo['dependencies']) ?
            $this->packageInfo['dependencies'] : false;
    }

    /**
     * @return Dependencies
     */
    function getDependencies()
    {
        if (!isset($this->packageInfo['dependencies'])) {
            $this->packageInfo['dependencies'] = array();
        }

        return new Dependencies($this, $this->packageInfo['dependencies']);
    }

    /**
     * @return Release
     */
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

        return new Release($this, $this->packageInfo[$t], $this->filelist);
    }

    /**
     * @return Compatible
     */
    function getCompatible()
    {
        $compatible = array();
        if (isset($this->packageInfo['compatible'])) {
            $compatible = $this->packageInfo['compatible'];
        }

        return new Compatible($this, $compatible);
    }

    /**
     * @return Configureoption
     */
    function getConfigureOption()
    {
        $configureoption = array();
        if (isset($this->packageInfo['configureoption'])) {
            $configureoption = $this->packageInfo['configureoption'];
            if (count($configureoption) && !isset($configureoption[0])) {
                $configureoption = array($configureoption);
            }
        }

        return new Configureoption($this, $configureoption);
    }

    /**
     * @return bool TRUE if validated, FALSE otherwise.
     */
    function getSchemaOK()
    {
        return $this->_schemaValidated;
    }

    function getArchiveFile()
    {
        return $this->_archiveFile;
    }

    /**
     * @return string
     */
    function getRequestedGroup()
    {
        return $this->requestedGroup;
    }

    function setRequestedGroup($var, $value)
    {
        $this->requestedGroup = $value;
    }

    /**
     * Directly set the array that defines this packagefile
     *
     * WARNING: no validation.  This should only be performed by internal
     * methods inside Pyrus or by inputting an array saved from an existing
     * Pyrus\PackageFile\v2.
     * 
     * @param array $pinfo
     */
    function fromArray($pinfo)
    {
        $this->_schemaValidated = true;
        $this->packageInfo = $pinfo['package'];
    }

    function fromPackageFile(PackageFileInterface $package)
    {
        $this->fromArray($package->toArray());
        $this->setFilelist($package->getFileList());
        $this->setBaseInstallDirs($package->getBaseInstallDirs());
    }

    function hasFile($file)
    {
        $file = str_replace('\\', '/', $file);
        return isset($this->filelist[$file]);
    }

    function getFile($file)
    {
        $file = str_replace('\\', '/', $file);
        return $this->filelist[$file];
    }

    function setFilelist(array $list)
    {
        $this->filelist = $list;
    }

    function getFileList()
    {
        return $this->filelist;
    }

    function setFilelistFile($file, $info)
    {
        $file = str_replace('\\', '/', $file);
        if ($info === null) {
            if (array_key_exists($file, $this->filelist)) {
                unset($this->filelist[$file]);
            }

            return;
        }
        $this->filelist[$file] = $info;
    }

    function setBaseInstallDirs(array $list)
    {
        $this->baseinstalldirs = $list;
    }

    function getBaseInstallDirs()
    {
        return $this->baseinstalldirs;
    }

    /**
     * @param string full path to file
     * @param string attribute name
     * @param string attribute value
     * @return bool success of operation
     */
    function setFileAttribute($filename, $attr, $value)
    {
        if (!in_array($attr, array('role', 'name', 'baseinstalldir', 'install-as', 'md5sum'), true)) {
            // check to see if this is a task
            if ($this->isValidTask($attr)) {
                if ($value instanceof Common) {
                    $value = $value->getInfo();
                }

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

            throw new Exception('Cannot set invalid attribute ' . $attr . ' for file ' . $filename);
        }

        if (!isset($this->filelist[$filename])) {
            throw new Exception('Cannot set attribute ' . $attr . ' for non-existent file ' . $filename);
        }

        if ($attr == 'name') {
            throw new Exception('Cannot change name of file ' .$filename);
        }

        $this->filelist[$filename]['attribs'][$attr] = $value;
    }

    /**
     * @param string $name
     * 
     * @return boolean TRUE on sucess, FALSE on failure.
     */
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
     * 
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
     * @return bool
     */
    function isNewPackage()
    {
        return version_compare($this->dependencies['required']->pearinstaller->min,
                               '2.0.0a1', '>=');
    }

    /**
     * Determines whether this package claims it is compatible with the version of
     * the package that has a recommended version dependency
     *
     * This function should only be called when the package has a recommended
     * version tag in a package or subpackage dependency on the package
     * represented by $pf, as no check is done to see whether $this
     * depends on $pf
     * 
     * @return boolean
     */
    function isCompatible(PackageFileInterface $pf)
    {
        if (!isset($this->packageInfo['compatible']) || !isset($this->packageInfo['channel'])) {
            return false;
        }

        $found = false;
        $package = strtolower($pf->name);
        foreach ($this->compatible as $info) {
            if (strtolower($info->name) == $package && $info->channel == $pf->channel) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return false;
        }

        $me = $pf->version['release'];
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

    /**
     * @param PackageFileInterface $p
     * 
     * @return bool
     */
    function isSubpackageOf(PackageFileInterface $p)
    {
        return $p->isSubpackage($this);
    }

    /**
     * Determines whether the passed in package is a subpackage of this package.
     *
     * No version checking is done, only name verification.
     * 
     * @return bool
     */
    function isSubpackage(PackageFileInterface $p)
    {
        $package = strtolower($p->name);
        foreach (array('required', 'optional', 'group') as $type) {
            if ($type === 'group') {
                foreach ($this->dependencies['group'] as $group) {
                    foreach ($group->subpackage as $dep) {
                        if (strtolower($dep->name) != $package) {
                            continue;
                        }

                        if (isset($dep->channel) && $dep->channel == $p->channel) {
                            return true;
                        } elseif ($dep->uri == $p->uri) {
                            return true;
                        }
                    }
                }
            }

            foreach ($this->dependencies[$type]->subpackage as $dep) {
                if (strtolower($dep->name) != $package) {
                    continue;
                }

                if (isset($dep->channel) && $dep->channel == $p->channel) {
                    return true;
                } elseif ($dep->uri == $p->uri) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 
     * @param PackageFileInterface $pkg
     * 
     * @return bool
     */
    function isEqual(PackageFileInterface $pkg)
    {
        if ($this->channel === '__uri') {
            return $pkg->name === $this->name && $pkg->uri === $this->uri;
        }

        return $pkg->name === $this->name && $pkg->channel === $this->channel;
    }

    /**
     * @param PackageFileInterface $pkg
     * 
     * @return bool Returns TRUE if any dependency, optional or required, exists
     *     on the package specified.
     */
    function dependsOn(PackageFileInterface $pkg)
    {
        $uri = $pkg->uri;
        $package = strtolower($pkg->name);
        $channel = strtolower($pkg->channel);

        $deps = $this->dependencies;
        foreach (array('package', 'subpackage') as $type) {
            foreach (array('required', 'optional') as $needed) {
                foreach ($deps[$needed]->$type as $dep) {
                    if (strtolower($dep->name) != $package) {
                        continue;
                    }

                    if (isset($dep->channel) && strtolower($dep->channel) == $channel) {
                        return true;
                    } elseif ($dep->uri === $uri) {
                        return true;
                    }
                }
            }

            foreach ($deps['group'] as $group) {
                foreach ($group->$type as $dep) {
                    if (strtolower($dep->name) != $package) {
                        continue;
                    }

                    if (isset($dep->channel) && strtolower($dep->channel) == $channel) {
                        return true;
                    } elseif ($dep->uri === $uri) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return string|bool One of "php", "extsrc", "extbin", "zendextsrc",
     *     "zendextbin", "bundle" or FALSE.
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

    /**
     * @return bool TRUE on success, FALSE on failure.
     */
    function hasDeps()
    {
        return isset($this->packageInfo['dependencies']);
    }

    /**
     * @return string
     */
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
     * @param array $toInstall an array of \Pyrus\Package objects
     * @param MultiErrors $errs
     */
    function validateDependencies(array $toInstall, MultiErrors $errs)
    {
        $dep = new Validator2($this->packageInfo['channel'] . '/' . $this->packageInfo['name'],
            Validate::DOWNLOADING, $errs);
        $dep->validatePhpDependency($this->dependencies['required']->php);
        $dep->validatePearinstallerDependency($this->dependencies['required']->pearinstaller);
        foreach (array('required', 'optional') as $required) {
            foreach ($this->dependencies[$required]->package as $d) {
                $dep->validatePackageDependency($d, $toInstall);
            }

            foreach ($this->dependencies[$required]->subpackage as $d) {
                $dep->validateSubpackageDependency($d, $toInstall);
            }

            foreach ($this->dependencies[$required]->extension as $d) {
                $dep->validateExtensionDependency($d);
            }
        }

        foreach ($this->dependencies['required']->arch as $d) {
            $dep->validateArchDependency($d);
        }

        foreach ($this->dependencies['required']->os as $d) {
            $dep->validateOsDependency($d);
        }
    }

    function getValidator($state = Validate::NORMAL)
    {
        return new Validator;
    }

    /**
     * @return string
     */
    function getTasksNs()
    {
        if (!isset($this->_tasksNs) && isset($this->packageInfo['attribs'])) {
            foreach ($this->packageInfo['attribs'] as $name => $value) {
                if ($value == 'http://pear.php.net/dtd/tasks-1.0') {
                    $this->_tasksNs = str_replace('xmlns:', '', $name);
                    break;
                }
            }
        }

        return $this->_tasksNs;
    }

    /**
     * @param string $file
     * 
     * @return string|boolean The base install dir, or FALSE on failure.
     */
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

        if ($value === null && isset($this->packageInfo[$var])) {
            unset($this->packageInfo[$var]);
            return;
        }

        $this->packageInfo[$var] = $value;
    }

    function setLicense($var, $value)
    {
        if ($value instanceof License) {
            return $this->rawlicense = $value->getInfo();
        }

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
            $this->rawlicense = array(
                'attribs' => array('uri' =>
                    $licensemap[strtolower($value)]),
                '_content' => $value
                );
        } else {
            // don't use bogus uri
            $this->rawlicense = (string) $value;
        }
    }

    function setType($var, $value)
    {
        if (!is_string($value)) {
            throw new Exception('package.xml type must be a ' .
            'string, was a ' . gettype($value));
        }

        if ($value != 'bundle') {
            $value .= 'release';
        }

        if (in_array($value, $a = array('phprelease', 'extsrcrelease', 'extbinrelease',
                                        'zendextsrcrelease', 'zendextbinrelease', 'bundle'))) {
            foreach ($a as $type) {
                if ($value == $type) {
                    if (!isset($this->packageInfo[$type])) {
                        $this->packageInfo[$type] = array();
                    }
                    continue;
                }

                if (isset($this->packageInfo[$type])) {
                    unset($this->packageInfo[$type]);
                }
            }
        }
    }

    function setRawConfigureopion($var, $value)
    {
        $t = $this->getPackageType();
        if ($t != 'bundle') {
            $t .= 'release';
        }

        if ($value === null) {
            if (isset($this->packageInfo[$t]['configureoption'])) {
                unset($this->packageInfo[$t]['configureoption']);
            }
        } else {
            $this->packageInfo[$t]['configureoption'] = $value;
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

                $this->packageInfo[$rel] = '';
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

            if ($value === null) {
                if (isset($this->packageInfo[$actual])) {
                    unset($this->packageInfo[$actual]);
                }
            } else {
                $this->packageInfo[$actual] = $value;
            }

            return;
        }

        throw new Exception('Cannot set ' . $var . ' directly');
    }

    /**
     * Return the contents of a tag.
     * 
     * @param string $name
     * 
     * @return mixed
     */
    protected function tag($name)
    {
        $tags = array('version', 'stability', 'providesextension', 'usesrole',
                      'usestask', 'srcpackage', 'srcuri',);
        if (!isset($this->packageInfo[$name]) && in_array($name, $tags, true)) {
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

                return new SimpleProperty($this, $info, $name);
        }

        if (!isset($this->packageInfo[$name])) {
            return false;
        }

        return $this->packageInfo[$name];
    }

    /**
     * Update the changelog based on the current information.
     * 
     * @return void
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

    /**
     * @return string
     */
    function __toString()
    {
        $this->packageInfo['attribs'] = $this->rootAttributes;
        $this->packageInfo['date'] = date('Y-m-d');
        $this->packageInfo['time'] = date('H:i:s');
        $arr = $this->toArray();
        return (string) new XMLWriter($arr);
    }

    /**
     * @return $this
     */
    function toRaw()
    {
        return $this;
    }

    /**
     * @param bool $forpackaging
     * 
     * @return array
     */
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
            PackagingIterator::setParent($this);
            $a = new PackagingIterator($a);
        }

        $temp = array();
        foreach ($a as $name => $stuff) {
            if ($forpackaging) {
                // map old to new name
                $temp[$stuff['attribs']['name']] = $name;
                if (isset($stuff['attribs']['baseinstalldir'])) {
                    unset($stuff['attribs']['baseinstalldir']);
                }
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
            $sortInstallAs = function ($a, $b) {
                return strnatcasecmp($a['attribs']['name'], $b['attribs']['name']);
            };

            if (!is_array($arr[$reltag])) {
                // do nothing
            } elseif (!isset($arr[$reltag][0])) {
                if (
                    isset($arr[$reltag]['filelist']) &&
                    isset($arr[$reltag]['filelist']['install']) &&
                    isset($arr[$reltag]['filelist']['install'][0])
                ) {
                    usort($arr[$reltag]['filelist']['install'], $sortInstallAs);
                }

                if (
                    isset($arr[$reltag]['filelist']) &&
                    isset($arr[$reltag]['filelist']['ignore']) &&
                    isset($arr[$reltag]['filelist']['ignore'][0])
                ) {
                    usort($arr[$reltag]['filelist']['ignore'], $sortInstallAs);
                }
            } else {
                foreach ($arr[$reltag] as $i => $contents) {
                    if (!isset($contents['filelist'])) {
                        continue;
                    }

                    if (
                        isset($contents['filelist']['install']) &&
                        isset($contents['filelist']['install'][0])
                    ) {
                        usort($arr[$reltag][$i]['filelist']['install'], $sortInstallAs);
                    }

                    if (
                        isset($contents['filelist']['ignore']) &&
                        isset($contents['filelist']['ignore'][0])
                    ) {
                        usort($arr[$reltag][$i]['filelist']['ignore'], $sortInstallAs);
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
