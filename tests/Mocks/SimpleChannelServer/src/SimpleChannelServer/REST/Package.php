<?php
/**
 * Package REST management class.
 *
 * This class should be serialized and re-loaded with each request in order to 
 * retain the list of packages in the channel
 *
 * @category Developer
 * @package  PEAR2_SimpleChannelServer
 * @author   Greg Beaver <cellog@php.net>
 * @license  New BSD?
 * @link     http://svn.php.net/viewvc/pear2/sandbox/SimpleChannelServer/
 */
class PEAR2_SimpleChannelServer_REST_Package extends
      PEAR2_SimpleChannelServer_REST_Manager
{
    private $_packages = array();

    /**
     * Save package REST based on a release
     *
     * @param \PEAR2\Pyrus\package $new
     */
    function save(\PEAR2\Pyrus\Package $new)
    {
        $this->_packages[$new->name] = true;
        $this->saveInfo($new);
        $this->saveAllPackages();
        $this->saveMaintainers($new);
    }

    /**
     * Remove package REST based on a release
     *
     * This does nothing
     * @param \PEAR2\Pyrus\package $new
     */
    function erase(\PEAR2\Pyrus\Package $new)
    {
    }

    /**
     * Mark a package as deprecated in favor of another package
     *
     * @param string $name
     * @param string $newpackage
     * @param string $newchannel
     */
    function deprecatePackage($name, $newpackage, $newchannel)
    {
        if (file_exists($this->rest . DIRECTORY_SEPARATOR . 'p' . DIRECTORY_SEPARATOR .
              strtolower($name) . DIRECTORY_SEPARATOR . 'info.xml')) {
            $oldinfo = $reader->parse($this->rest . DIRECTORY_SEPARATOR . 'p' .
                DIRECTORY_SEPARATOR . 'info.xml');
        }
        $oldinfo['p']['dc'] = $newchannel;
        $oldinfo['p']['dp'] = $newpackage;
        $this->savePackageREST(strtolower($name) . DIRECTORY_SEPARATOR . 'info.xml',
            $oldinfo);
    }

    /**
     * Remove a package from the REST list
     * 
     * @param unknown_type $name
     */
    function deletePackage($name)
    {
        unset($this->_packages[$name]);
        @unlink($this->rest . DIRECTORY_SEPARATOR . 'p' .
            DIRECTORY_SEPARATOR . 'info.xml');
        $this->saveAllPackages();
    }

    /**
     * Save package REST based on a release
     * 
     * @param \PEAR2\Pyrus\Package $new
     */
    function saveInfo(\PEAR2\Pyrus\Package $new)
    {
        $reader = new \PEAR2\Pyrus\XMLParser;
        $deprecated = false;
        if (file_exists($this->rest . DIRECTORY_SEPARATOR . 'p' . DIRECTORY_SEPARATOR .
              'info.xml')) {
            $oldinfo = $reader->parse($this->rest . DIRECTORY_SEPARATOR . 'p' .
                DIRECTORY_SEPARATOR . 'info.xml');
            if (isset($oldinfo['p']['dp'])) {
                $deprecated = array('dp' => $oldinfo['p']['dp'], 'dc' => $oldinfo['p']['dc']);
            }
        }
        $xml = array();
        $xml['n'] = $new->name;
        $xml['c'] = $this->channel;
        try {
            $category = PEAR2_SimpleChannelServer_Categories::getPackageCategory($new->name);
        } catch (PEAR2_SimpleChannelServer_Categories_Exception $e) {
            $categories = PEAR2_SimpleChannelServer_Categories::create('Default', 'This is the default category');
            $categories->linkPackageToCategory($new->name,'Default');
            $category = PEAR2_SimpleChannelServer_Categories::getPackageCategory($new->name);
        }
        $xml['ca'] = array(
            'attribs' => array('xlink:href' => $this->getCategoryRESTLink($category)),
            '_content' => $category,
            );
        $xml['l'] = $new->license['name'];
        $xml['s'] = $new->summary;
        $xml['d'] = $new->description;
        $xml['r'] = array('attribs' => 
            $this->getReleaseRESTLink(strtolower($new->name)));
        if ($a = $new->extends) {
            $xml['pa'] = array('attribs' =>
                array('xlink:href' => $this->getPackageRESTLink(strtolower($a) . '/info.xml')),
                '_content' => $a);
        }
        $xmlinf = $this->_getProlog('p', 'package');
        $xml['attribs'] = $xmlinf['p']['attribs'];
        $xml = array('p' => $xml);
        $this->savePackageREST(strtolower($new->name) . '/info.xml', $xml);
    }

    /**
     * Save a list of all packages in REST
     *
     * This is not release dependent.
     */
    function saveAllPackages()
    {
        $xml = $this->_getProlog('a', 'allpackages');
        $xml['a']['p'] = array();
        foreach (new DirectoryIterator($this->rest . 'p') as $file) {
            if ($file->isDot()) continue;
            $a = (string) $file;
            if ($file->isDir() && $a[0] != '.') {
                $xml['a']['p'][] = $a;
            }
        }
        usort($xml['a']['p'], 'strnatcmp');
        $this->savePackageREST('packages.xml', $xml);
    }

    /**
     * Save package maintainers information for this release
     *
     * @param \PEAR2\Pyrus\Package $new package to be saved
     *
     * @return void
     */
    function saveMaintainers(\PEAR2\Pyrus\Package $new)
    {
        $m  = $this->_getProlog('m', 'packagemaintainers');
        $m2 = $this->_getProlog('m', 'packagemaintainers2');

        $m['m']['p'] = $m2['m']['p'] = $new->name;
        $m['m']['c'] = $m2['m']['c'] = $this->chan;
        $m['m']['m'] = $m2['m']['m'] = array();

        foreach ($new->allmaintainers as $role => $maintainers) {
            if (!$maintainers) continue;
            foreach ($maintainers as $dev) {
                $m['m']['m'][]  = array('h' => $dev->user, 'a' => $dev->active);
                $m2['m']['m'][] = array(
                    'h' => $dev->user,
                    'a' => $dev->active,
                    'r' => $role
                );
            }
        }
        $this->savePackageREST(strtolower($new->name) . '/maintainers.xml',
            $m);
        $this->savePackageREST(strtolower($new->name) . '/maintainers2.xml',
            $m2);
    }
}