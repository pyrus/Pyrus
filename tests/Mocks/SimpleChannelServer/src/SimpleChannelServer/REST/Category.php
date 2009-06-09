<?php
/**
 * Class for managing category information for the PEAR channel.
 *
 * @category Developer
 * @package  PEAR2_SimpleChannelServer
 * @author   Greg Beaver <cellog@php.net>
 * @license  New BSD?
 * @link     http://svn.pear.php.net/wsvn/PEARSVN/sandbox/SimpleChannelServer/
 */
class PEAR2_SimpleChannelServer_REST_Category extends
      PEAR2_SimpleChannelServer_REST_Manager
{
    /**
     * Construct a new rest category object
     * 
     * @param string $savepath   full path to REST files
     * @param string $channel    the channel name
     * @param string $serverpath relative path within URI to REST files
     */
    function __construct($savepath, $channel, $serverpath = 'rest/')
    {
        parent::__construct($savepath, $channel, $serverpath);
    }

    /**
     * Save a package release's REST-related information
     *
     * @param PEAR2_Pyrus_Package $new Package to save category for
     * 
     * @return void
     */
    function save(PEAR2_Pyrus_Package $new)
    {
        $category = PEAR2_SimpleChannelServer_Categories::getPackageCategory($new->name);
        $this->savePackagesInfo($category);
        $this->saveAllCategories();
    }

    /**
     * Delete a package release's REST-related information
     *
     * @param PEAR2_Pyrus_Package $new Package to rease rest info for
     * 
     * @return void
     */
    function erase(PEAR2_Pyrus_Package $new)
    {
        $category = PEAR2_SimpleChannelServer_Categories::getPackageCategory($new->name);
        $this->savePackagesInfo($category);
    }

    /**
     * Save REST xml information for all categories
     * 
     * This is not release-dependent
     * 
     * @return void
     */
    function saveAllCategories()
    {
        $categories     = PEAR2_SimpleChannelServer_Categories::getCategories();
        $xml            = $this->_getProlog('a', 'allcategories');
        $xml['a']['ch'] = $this->channel;
        $xml['a']['c']  = array();
        if (count($categories) == 1) {
            $xml['a']['c'] = array('attribs' =>
                array('xlink:href' =>
                    $this->getCategoryRESTLink(urlencode(key($categories))
                        . '/info.xml')),
                '_content' => key($categories));
                $this->saveInfo(key($categories),
                                $categories[key($categories)]['desc'],
                                $categories[key($categories)]['alias']);
        } else {
            foreach ($categories as $category => $data) {
                $xml['a']['c'] = array(
                    'attribs' => array(
                        'xlink:href' =>
                        $this->getCategoryRESTLink(urlencode($category) . '/info.xml')),
                    '_content' => $category,
                );
                $this->saveInfo($category, $data['desc'], $data['alias']);
            }
        }
        $this->saveCategoryREST('categories.xml', $xml);
    }

    /**
     * Save information on a category
     * 
     * This is not release-dependent
     *
     * @param string $category The name of the category eg:Services
     * @param string $desc     Basic description for the category
     * @param string $alias    Optional alias category name
     * 
     * @return void
     */
    function saveInfo($category, $desc, $alias = false)
    {
        PEAR2_SimpleChannelServer_Categories::create($category, $desc, $alias);
        $xml           = $this->_getProlog('c', 'category');
        $xml['c']['n'] = $category;
        $xml['c']['a'] = $alias ? $category : $alias;
        $xml['c']['c'] = $this->channel;
        $xml['c']['d'] = $desc;
        $this->saveCategoryREST($category . '/info.xml', $xml);
    }

    /**
     * Save packagesinfo.xml for a category
     *
     * @param string $category Category to update packages info for
     * 
     * @return void
     */
    function savePackagesInfo($category)
    {
        $xml  = array();
        $pdir = $this->rest . DIRECTORY_SEPARATOR . 'p';
        $rdir = $this->rest . DIRECTORY_SEPARATOR . 'r';

        $packages = PEAR2_SimpleChannelServer_Categories::packagesInCategory($category);
        $reader   = new PEAR2_Pyrus_XMLParser;
        clearstatcache();
        $xml['pi'] = array();
        foreach ($packages as $package) {
            $next = array();
            if (!file_exists($pdir . DIRECTORY_SEPARATOR . strtolower($package['name']) .
                    DIRECTORY_SEPARATOR . 'info.xml')) {
                continue;
            }
            $f = $reader->parse($pdir . DIRECTORY_SEPARATOR . strtolower($package['name']) .
                    DIRECTORY_SEPARATOR . 'info.xml');
            unset($f['p']['attribs']);
            $next['p'] = $f['p'];
            if (file_exists($rdir . DIRECTORY_SEPARATOR . strtolower($package['name']) .
                    DIRECTORY_SEPARATOR . 'allreleases.xml')) {
                $r = $reader->parse($rdir . DIRECTORY_SEPARATOR .
                        strtolower($package['name']) . DIRECTORY_SEPARATOR .
                        'allreleases.xml');
                unset($r['a']['attribs']);
                unset($r['a']['p']);
                unset($r['a']['c']);
                $next['a'] = $r['a'];
                $dirhandle = opendir($rdir . DIRECTORY_SEPARATOR .
                    strtolower($package['name']));
                while (false !== ($entry = readdir($dirhandle))) {
                    if (strpos($entry, 'deps.') === 0) {
                        $version = str_replace(array('deps.', '.txt'), array('', ''), $entry);
                        
                        $next['deps']      = array();
                        $next['deps']['v'] = $version;
                        $next['deps']['d'] = file_get_contents($rdir . DIRECTORY_SEPARATOR .
                            strtolower($package['name']) . DIRECTORY_SEPARATOR .
                            $entry);
                    }
                }
            }
            $xml['pi'][] = $next;
        }
        $xmlinf        = $this->_getProlog('f', 'categorypackageinfo');
        $xmlinf['f'][] = $xml;
        $this->saveCategoryREST($category . DIRECTORY_SEPARATOR . 'packagesinfo.xml', $xmlinf);
    }
}