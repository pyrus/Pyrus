<?php
/**
 * PEAR2_Pyrus_Registry_Sqlite3_Package
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
 * Package within the sqlite3 registry
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Registry_Sqlite3_Package extends PEAR2_Pyrus_PackageFile_v2 implements ArrayAccess, PEAR2_Pyrus_IPackageFile
{

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
        'api-version' => 'basicVar',
        'release-version' => 'basicVar',
        'api-state' => 'basicVar',
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
        'usesrole' => 'getUsesRoleTask',
        'usestask' => 'getUsesRoleTask',
        'srcpackage' => 'tag',
        'srcchannel' => 'tag',
        'srcuri' => 'tag',
        'name' => 'basicVar',
        'summary' => 'basicVar',
        'description' => 'basicVar',
        'date' => 'basicVar',
        'time' => 'basicVar',
        'notes' => 'basicVar',
    );

    protected $packagename;
    protected $package;
    protected $channel;
    protected $sqlite3;
    protected $reg;

    function __construct(PEAR2_Pyrus_Registry_Sqlite3 $cloner)
    {
        $this->reg = $cloner;
        $this->sqlite3 = $cloner->getDatabase();
    }

    function offsetExists($offset)
    {
        $info = PEAR2_Pyrus_Config::current()->channelregistry->parseName($offset);
        return $this->reg->exists($info['package'], $info['channel']);
    }

    function offsetGet($offset)
    {
        $this->packagename = $offset;
        $info =  PEAR2_Pyrus_Config::current()->channelregistry->parseName($this->packagename);
        $this->package = $info['package'];
        $this->channel = $info['channel'];
        $ret = clone $this;
        unset($this->packagename);
        unset($this->package);
        unset($this->channel);
        return $ret;
    }

    function offsetSet($offset, $value)
    {
        if ($offset == 'install') {
            $this->reg->install($value);
        }
    }

    function offsetUnset($offset)
    {
        $info = PEAR2_Pyrus_Config::current()->channelregistry->parseName($offset);
        $this->reg->uninstall($info['package'], $info['channel']);
    }

    function __get($var)
    {
        if (!isset($this->packagename)) {
            throw new PEAR2_Pyrus_Registry_Exception('Attempt to retrieve ' . $var .
                ' from unknown package');
        }
        return parent::__get($var);
    }

    function getChannel($var)
    {
        return $this->channel;
    }

    function basicVar($var)
    {
        return $this->reg->info($this->package, $this->channel, $var);
    }

}