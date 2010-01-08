<?php
/**
 * \pear2\Pyrus\Package\Dependency\Set\PackageTree
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Class to represent vertices within the dependency directed graph.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus\Package\Dependency\Set;
class PackageTree
{
    /**
     * Optional dependencies that won't be installed
     *
     * Informational list of optional depenndencies that
     * will not be installed without the --optionaldeps flag
     * @var array
     */
    protected static $optionalDeps = array();

    static protected $availableVersionsMap = array();
    static protected $localPackages = array();
    static protected $allNodes = array();
    static protected $allDeps = array();
    static protected $errors = array();
    protected $parent;
    protected $set;
    protected $node;
    protected $name;
    protected $compositeDep;
    protected $compositeConflicts;
    protected $versionsAvailable = array();
    protected $allVersions = array();
    protected $children = array();
    protected $allchildren = array();
    protected $firstVersion;
    static protected $dirtyMap = array();

    function __construct(\pear2\Pyrus\Package\Dependency\Set $set,
                         \pear2\Pyrus\Package $node, PackageTree $parent = null)
    {
        $this->set    = $set;
        $this->node   = $node;
        $this->parent = $parent;
        $this->name   = $node->channel . '/' . $node->name;
        if ($node->isRemote() && $node->isAbstract()) {
            if ($node->getExplicitVersion()) {
                $this->firstVersion = $node->getExplicitVersion();
                if (isset(static::$availableVersionsMap[$this->name])) {
                    $rebuild = true;
                } else {
                    $rebuild = false;
                }
                $this->allVersions =
                $this->versionsAvailable =
                    array($node->version['release']);
                $this->setAvailableVersionsMap();
                if ($rebuild) {
                    $this->rebuild($this->name);
                }
                self::register($this);
                return;
            }
            if (isset(self::$availableVersionsMap[$this->name])) {
                $this->allVersions = $this->versionsAvailable =
                    self::$availableVersionsMap[$this->name];
            } else {
                while (!($node instanceof \pear2\Pyrus\Channel\RemotePackage)) {
                    $node = $node->getInternalPackage();
                }
                foreach ($node->getReleaseList() as $info) {
                    $this->allVersions[] = $info['v'];
                    $this->versionsAvailable[] = $info['v'];
                }
                if (null === $parent) {
                    return $this->findParentVersion();
                }
                $this->setAvailableVersionsMap();
            }
        } else {
            $this->versionsAvailable = array($node->version['release']);
            $this->allVersions = array($node->version['release']);
            $this->setAvailableVersionsMap();
        }
        self::register($this);
    }

    protected function findParentVersion()
    {
        $notset = true;
        do {
            if (!$this->determineInitialVersion()) {
                array_pop($this->versionsAvailable);
            } else {
                $notset = false;
            }
        } while ($notset && count($this->versionsAvailable));
        if (count($this->versionsAvailable)) {
            $this->setAvailableVersionsMap();
            self::register($this);
            return;
        }
        throw new Exception('Unable to find a compatible release for ' .
                            $this->name);
    }

    static function setLocalPackages(array $packages)
    {
        self::$optionalDeps         = array();
        self::$errors               = array();
        self::$localPackages        = array();
        self::$availableVersionsMap = array();
        self::$localPackages        = $packages;
    }

    function synchronize()
    {
        if (isset($this->compositeDep)) {
            if (!$this->compositeDep->equals($this->set->getCompositeDependency($this->node))
                || !$this->compositeConflicts->equals($this->set->getCompositeDependency($this->node, true))) {
                if (null === $this->parent) {
                    $this->findParentVersion();
                } else {
                    // tell rebuildIfNecessary() that rebuilding is necessary
                    $this->versionsAvailable = array();
                    $this->rebuildIfNecessary($this->name);
                }
            }
        }
        foreach ($this->children as $child) {
            $child->synchronize();
        }
    }

    static function resetDirtyMap()
    {
        self::$dirtyMap = array();
    }

    protected function setAvailableVersionsMap()
    {
        if (!isset(self::$availableVersionsMap[$this->name])) {
            self::$availableVersionsMap[$this->name] = $this->versionsAvailable;
        } elseif (self::$availableVersionsMap[$this->name] != $this->versionsAvailable) {
            self::$dirtyMap[$this->name] = true;
            self::$availableVersionsMap[$this->name] = $this->versionsAvailable;
        }
    }

    static protected function register($obj)
    {
        self::$allNodes[$obj->name][] = $obj;
    }

    static protected function unregister($obj)
    {
        if (!isset(self::$allNodes[$obj->name])) {
            throw new Exception('Internal error: ' . $obj->name . ' is ' .
                                'being unregistered, but is not registered');
        }
        foreach (self::$allNodes[$obj->name] as $i => $test) {
            if ($test === $obj) {
                unset(self::$allNodes[$obj->name][$i]);
                if (!count(self::$allNodes[$obj->name])) {
                    unset(self::$allNodes[$obj->name]);
                }
                return;
            }
        }
    }

    static function dirtyNodes()
    {
        return array_keys(self::$dirtyMap);
    }

    /**
     * Rebuild if necessary
     * 
     * @param string $nodename eg: pear.php.net/Spreadsheet_Excel_Writer
     */
    function rebuildIfNecessary($nodename)
    {
        if ($this->name == $nodename) {
            if ($this->versionsAvailable != self::$availableVersionsMap[$this->name]) {
                $this->prune();
                $this->versionsAvailable = self::$availableVersionsMap[$this->name];
                if (!count($this->versionsAvailable)) {
                    $this->throwDepFailException();
                }
                if ($this->node->isRemote()) {
                    $this->node->resetConcreteVersion();
                }
                if (!$this->determineInitialVersion()) {
                    $this->throwDepFailException();
                }
                // check to see if the new version now is the same as what we have installed
                if ($this->isUpgradeable()) {
                    $installedversion = \pear2\Pyrus\Config::current()->registry->info($this->node->name,
                                                                                       $this->node->channel,
                                                                                       'version');
                    if ($installedversion === $this->node->version['release']) {
                        $this->prune();
                        $this->parent->removeChild($this);
                        return;
                    }
                }
                $this->populate();
            }
        } else {
            foreach ($this->children as $child) {
                $child->rebuildIfNecessary($nodename);
            }
        }
    }

    function has($name)
    {
        if ($this->name == $name) {
            return true;
        }
        foreach ($this->children as $child) {
            if ($child->has($name)) {
                return true;
            }
        }
        return false;
    }

    function prune()
    {
        foreach ($this->children as $child) {
            $child->prune();
            $this->removeChild($child);
        }
    }

    function throwDepFailException()
    {
        if (isset(self::$errors[$this->name])) {
            $extra = implode("\n", self::$errors[$this->name]);
        }
        throw new Exception('No versions of ' . $this->name .
                            ' or of its dependencies that can be installed because of' .
                            $extra);
    }

    function getUnsatisfiedString()
    {
        $dep = $this->set->getCompositeDependency($this->node);
        $conflicting = $this->set->getCompositeDependency($this->node, true);
        $unsatisfied = array_merge($dep->getUnsatisfiedSources($this->version['release']),
                                   $conflicting->getUnsatisfiedSources($this->version['release']));
        $fullinfo = '';
        foreach ($unsatisfied as $dep) {
            $fullinfo .= $dep->getPackageFile()->channel . '/' . $dep->getPackageFile()->name . " depends on: $dep\n";
        }
        return ":\n$fullinfo";
    }

    protected function determineInitialVersion($returnFalseOnVersionChange = false)
    {
        if (!$this->node->isRemote()) {
            // anything downloaded or local is good
            return true;
        }
        $this->compositeDep = $dep = $this->set->getCompositeDependency($this->node);
        $this->compositeConflicts = $conflicting = $this->set->getCompositeDependency($this->node, true);
        if (!count($this->versionsAvailable)) {
            $this->throwDepFailException();
        }
        try {
            if (true === $this->node->figureOutBestVersion($dep, $this->versionsAvailable,
                                                           $conflicting)) {
                // we just changed version from a previously calculated version,
                // so restart
                if ($returnFalseOnVersionChange) {
                    return false;
                }
                return true;
            }
        } catch (\pear2\Pyrus\Channel\Exception $e) {
            if ($this->parent) {
                $this->parent->saveError($this);
            }
            return false;
        }
        return true;
    }

    function saveError(PackageTree $child)
    {
        if (!isset(self::$errors[$this->name])) {
            self::$errors[$this->name] = array();
        }
        if (!isset(self::$errors[$this->name][$this->version['release']])) {
            self::$errors[$this->name][$this->version['release']] = '';
        }
        self::$errors[$this->name][$this->version['release']] .= $child->getUnsatisfiedString();
    }

    function removeThisVersion()
    {
        $available = array_flip(self::$availableVersionsMap[$this->name]);
        unset($available[$this->node->version['release']]);
        $old = $this->versionsAvailable;
        $this->versionsAvailable = array_flip($available);
        $this->setAvailableVersionsMap();
        // reset so we get re-built
        $this->versionsAvailable = $old;
    }

    /**
     * Populate the dependency tree with the selected version or an explicit version
     */
    function populate()
    {
        $this->populateBranch();
        foreach ($this->children as $child) {
            if (!$child->determineInitialVersion(true)) {
                $this->removeThisVersion();
                return false;
            }
        }
        foreach ($this->children as $child) {
            if (!$child->populate()) {
                return false;
            }
        }
        return true;
    }

    protected function populateBranch()
    {
        $package = $this->node;
        foreach (array('package', 'subpackage') as $p) {
            foreach ($package->dependencies['required']->$p as $dep) {
                self::$dirtyMap[$dep->channel . '/' . $dep->name] = true;
                if ($dep->conflicts) {
                    continue;
                }
                $this->retrieve($dep);
            }
        }
        if ($package->requestedGroup) {
            foreach (array('package', 'subpackage') as $p) {
                foreach ($package->dependencies['group']->{$package->requestedGroup}->$p as $dep) {
                    self::$dirtyMap[$dep->channel . '/' . $dep->name] = true;
                    $this->retrieve($dep);
                }
            }
        }
        foreach (array('package', 'subpackage') as $p) {
            foreach ($package->dependencies['optional']->$p as $dep) {
                if (!isset(\pear2\Pyrus\Main::$options['optionaldeps'])) {
                    if (!isset(static::$optionalDeps[$dep->channel . '/' . $dep->name])) {
                        static::$optionalDeps[$dep->channel . '/' . $dep->name] = array();
                    }
                    static::$optionalDeps[$dep->channel . '/' . $dep->name][$package->channel . '/' .$package->name]
                        = 1;
                    continue;
                }
                self::$dirtyMap[$dep->channel . '/' . $dep->name] = true;
                $this->retrieve($dep);
            }
        }
    }

    static function getUnusedOptionalDeps()
    {
        return self::$optionalDeps;
    }

    static function getPHPVersion()
    {
        return phpversion();
    }

    /**
     * Check to see if any packages in the list of packages to be installed
     * satisfy this dependency, and return one if found, otherwise
     * instantiate a new dependency package object
     * @return \pear2\Pyrus\PackageInterface|NULL
     */
    function retrieve(\pear2\Pyrus\PackageFile\v2\Dependencies\Package $info)
    {
        if (isset(self::$localPackages[$info->channel . '/' . $info->name])
                || $this->childProcessed($info->channel . '/' . $info->name)) {
            // we can safely ignore this dependency, an explicit local
            // package is being installed, and we will use it
            // or the dependency has been previously processed, and we will
            // simply result in a duplicate
            return;
        }
        $reg = \pear2\Pyrus\Config::current()->registry;
        // first check to see if the dependency is installed
        $canupgrade = false;
        if (isset($reg->package[$info->channel . '/' . $info->name])) {
            if (!isset(\pear2\Pyrus\Main::$options['upgrade'])) {
                // we don't attempt to upgrade a dep unless we're upgrading
                return;
            }
            $version = $reg->info($info->name, $info->channel, 'version');
            $stability = $reg->info($info->name, $info->channel, 'state');
            if ($this->node->isRemote() && $this->node->getExplicitState()) {
                $installedstability = \pear2\Pyrus\Installer::betterStates($stability);
                $parentstability = \pear2\Pyrus\Installer::betterStates($this->node->getExplicitState());
                if (count($parentstability) > count($installedstability)) {
                    $stability = $this->node->getExplicitState();
                }
            } else {
                $installedstability = \pear2\Pyrus\Installer::betterStates($stability);
                $prefstability = \pear2\Pyrus\Installer::betterStates(\pear2\Pyrus\Config::current()->preferred_state);
                if (count($prefstability) > count($installedstability)) {
                    $stability = \pear2\Pyrus\Config::current()->preferred_state;
                }
            }
            // see if there are new versions in our stability or better
            if (isset($info->uri)) {
                return;
            }
            $remote = new \pear2\Pyrus\Channel\RemotePackage(\pear2\Pyrus\Config::current()
                                                            ->channelregistry[$info->channel], $stability);
            $found = false;
            foreach ($remote[$info->name] as $remoteversion => $rinfo) {
                if (version_compare($remoteversion, $version, '<=')) {
                    continue;
                }
                if (version_compare($rinfo['minimumphp'], static::getPHPversion(), '>')) {
                    continue;
                }
                // found one, so upgrade is possible if dependencies pass
                $found = true;
                break;
            }
            // the installed package version satisfies this dependency, don't do anything
            if (!$found) {
                return;
            }
            $canupgrade = true;
        }
        if (isset($info->uri)) {
            $ret = new \pear2\Pyrus\Package\Remote($info->uri);
            // set up the basics
            $ret->name = $info->name;
            $ret->uri = $info->uri;
            $this->addChild($ret);
            return;
        }
        if ($this->node->isRemote() && $this->node->getExplicitState()) {
            // pass the same explicit state to the child dependency
            $ret = new \pear2\Pyrus\Package\Remote($info->channel . '/' . $info->name . '-' .
                                                  $this->node->getExplicitState());
            if ($canupgrade) {
                $ret->setUpgradeable();
            }
            $this->addChild($ret);
            return;
        }
        $ret = new \pear2\Pyrus\Package\Remote($info->channel . '/' . $info->name);
        if ($canupgrade) {
            $ret->setUpgradeable();
        }
        $this->addChild($ret);
        return;
    }

    protected function addChild($obj)
    {
        $child = new self($this->set, $obj, $this);
        $this->children[] = $child;
    }

    protected function removeChild($obj)
    {
        foreach ($this->children as $i => $test) {
            if ($obj === $test) {
                unset($this->children[$i]);
                $this->children = array_values($this->children);
                break;
            }
        }
        self::unregister($obj);
        $this->top()->unprocessChild($obj);
    }

    /**
     * This method stops dependency cycles from causing infinite recursion.
     */
    protected function childProcessed($obj)
    {
        if ($this->parent) {
            return $this->parent->childProcessed($obj);
        }
        if ($obj instanceof self) {
            $name = $obj->name();
        } else {
            $name = $obj;
        }
        if (isset($this->allchildren[$name])) {
            return true;
        }
        $this->allchildren[$name] = true;
        return false;
    }

    protected function unprocessChild($obj)
    {
        unset($this->allchildren[$obj->name()]);
    }

    function name()
    {
        return $this->name;
    }

    function top()
    {
        if (!$this->parent) {
            return $this;
        }
        return $this->parent->top();
    }

    function getPackageSet($fromParent = array())
    {
        if (!$fromParent && $this->parent) {
            return $this->parent->getPackageSet();
        }
        $ret = $fromParent;
        if (!$this->parent) {
            $ret[$this->name] = $this;
        }
        foreach ($this->children as $child) {
            $ret[$child->name()] = $child;
            $ret = $child->getPackageSet($ret);
        }
        return $ret;
    }

    function getDependencies(array $deps, $name)
    {
        foreach ($this->getPackageSet() as $package) {
            if ($package->channel . '/' . $package->name == $name
                || ($package->isRemote() && !$package->hasConcreteVersion())) {
                continue;
            }
            foreach (array('package', 'subpackage') as $p) {
                foreach ($package->dependencies['required']->$p as $dep) {
                    if ($dep->channel . '/' . $dep->name != $name) {
                        continue;
                    }
                    $deps[] = $dep;
                }
            }
            if ($package->requestedGroup) {
                foreach (array('package', 'subpackage') as $p) {
                    foreach ($package->dependencies['group']->{$package->requestedGroup}->$p as $dep) {
                        if ($dep->channel . '/' . $dep->name != $name) {
                            continue;
                        }
                        $deps[] = $dep;
                    }
                }
            }
            foreach (array('package', 'subpackage') as $p) {
                foreach ($package->dependencies['optional']->$p as $dep) {
                    if ($dep->channel . '/' . $dep->name != $name) {
                        continue;
                    }
                    $deps[] = $dep;
                }
            }
        }
        return $deps;
    }

    function __call($func, $args)
    {
        return call_user_func_array(array($this->node, $func), $args);
    }

    function __get($var)
    {
        if ($var == 'node') {
            return $this->node;
        }
        return $this->node->$var;
    }

    function __set($var, $value)
    {
        $this->node->$var = $value;
    }

    function __toString()
    {
        return $this->toString();
    }

    protected function toString($pad = '')
    {
        $ret = $pad . $this->name . '-' . $this->version['release'] . ":\n";
        $deps = '';
        foreach ($this->node->dependencies['required']->package as $dep) {
            $deps .= " ${pad}dep: $dep\n";
        }
        foreach ($this->node->dependencies['required']->subpackage as $dep) {
            $deps .= " ${pad}dep: $dep\n";
        }
        foreach ($this->node->dependencies['optional']->package as $dep) {
            $deps .= " ${pad}dep: $dep\n";
        }
        foreach ($this->node->dependencies['optional']->subpackage as $dep) {
            $deps .= " ${pad}dep: $dep\n";
        }
        foreach ($this->children as $child) {
            $deps .= $child->toString("$pad  ");
        }
        if (!$deps) {
            return $pad . $this->name . '-' . $this->version['release'] . ";\n";
        }
        return $ret . $deps;
    }
}