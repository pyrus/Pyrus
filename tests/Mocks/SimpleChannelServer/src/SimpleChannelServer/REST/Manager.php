<?php
/**
 * Base class for managing a REST based PEAR compatible channel server.
 *
 * @category Developer
 * @package  PEAR2_SimpleChannelServer
 * @author   Greg Beaver <cellog@php.net>
 * @license  New BSD?
 * @link     http://svn.php.net/viewvc/pear2/sandbox/SimpleChannelServer/
 */
class PEAR2_SimpleChannelServer_REST_Manager
{
    /**
     * Full path on the filesystem to the REST files
     *
     * @var string
     */
    protected $rest;
    /**
     * Relative path to REST files for URI link construction
     *
     * @var string
     */
    protected $uri;
    /**
     * Channel name this REST server applies to
     *
     * @var string
     */
    protected $chan;

    /**
     * @param string $savepath   full path to REST files
     * @param string $channel    the channel name
     * @param string $serverpath relative path within URI to REST files
     * @param array  $admins     an array of handles that are channel administrators
     *                           and can release/delete any package
     */
    function __construct($savepath, $channel, $serverpath = 'rest/')
    {
        $this->rest = $savepath;
        if (!file_exists($savepath)) {
            if (!@mkdir($savepath, 0777, true)) {
                throw new PEAR2_SimpleChannelServer_Exception('Could not initialize' .
                    'REST storage directory "' . $savepath . '"');
            }
        }
        $this->uri     = $serverpath;
        $this->chan    = $channel;
    }

    /**
     * Save release REST for a new package release.
     * 
     * Prior to calling this, categories and category package links
     * should be set up, otherwise the package will be released under
     * the "Default" category. 
     *
     * @param \pear2\Pyrus\Package $release
     * @param string              $releaser handle of person who is uploading this release
     */
    function saveRelease(\pear2\Pyrus\Package $new, $releaser)
    {
        if ($new->channel !== $this->chan) {
            throw new PEAR2_SimpleChannelServer_Exception('Cannot release ' .
                $new->name . '-' . $new->version['release'] . ', we are managing ' .
                $this->chan . ' channel, and package is in ' .
                $new->channel . ' channel');
        }
        if (!isset($new->maintainer[$releaser]) ||
            $new->maintainer[$releaser]->role !== 'lead') {
            throw new PEAR2_SimpleChannelServer_Exception($releaser . ' is not a ' .
                'lead maintainer of this package, and cannot release');
        }
        $category = new PEAR2_SimpleChannelServer_REST_Category($this->rest, $this->chan,
            $this->uri);
        $package = new PEAR2_SimpleChannelServer_REST_Package($this->rest, $this->chan,
            $this->uri);
        $maintainer = new PEAR2_SimpleChannelServer_REST_Maintainer($this->rest, $this->chan,
            $this->uri);
        $release = new PEAR2_SimpleChannelServer_REST_Release($this->rest, $this->chan,
            $this->uri);
        $maintainer->save($new);
        $package->save($new);
        $release->save($new, $releaser);
        $category->save($new);
    }

    /**
     * Remove a release from package REST
     * 
     * Removes REST.  If $deleteorphaned is true, then
     * maintainers who no longer maintain a package will be
     * deleted from package maintainer REST.
     * @param \pear2\Pyrus\Package $release
     * @param string $deleter handle of maintainer deleting this release
     * @param bool $deleteorphaned
     */
    function deleteRelease(\pear2\Pyrus\Package $release, $deleter, $deleteorphaned = true)
    {
        if ($new->channel !== $this->chan) {
            throw new PEAR2_SimpleChannelServer_Exception('Cannot delete release ' .
                $new->name . '-' . $new->version['release'] . ', we are managing ' .
                $this->chan . ' channel, and package is in ' .
                $new->channel . ' channel');
        }
        if (!isset($this->_admins[$releaser]) && (!isset($new->maintainer[$releaser]) ||
              $new->maintainer[$releaser]->role !== 'lead')) {
            throw new PEAR2_SimpleChannelServer_Exception($releaser . ' is not a ' .
                'lead maintainer of this package, and cannot delete the release');
        }
        $category = new PEAR2_SimpleChannelServer_REST_Category($this->rest, $this->chan,
            $this->uri);
        $package = new PEAR2_SimpleChannelServer_REST_Package($this->rest, $this->chan,
            $this->uri);
        $maintainer = new PEAR2_SimpleChannelServer_REST_Maintainer($this->rest, $this->chan,
            $this->uri);
        $release = new PEAR2_SimpleChannelServer_REST_Release($this->rest, $this->chan,
            $this->uri);
        $maintainer->erase($new, $deleteorphaned);
        $package->erase($new);
        $release->erase($new);
        $category->erase($new);
    }

    function __get($var)
    {
        if ($var == 'path') {
            return $this->uri;
        }
        if ($var == 'channel') {
            return $this->chan;
        }
    }

    protected function _getProlog($basetag, $schema)
    {
        return array($basetag => array(
                'attribs' =>
                    array(
                        'xmlns' => 'http://pear.php.net/dtd/rest.' . $schema,
                        'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                        'xmlns:xlink' => 'http://www.w3.org/1999/xlink',
                        'xsi:schemaLocation' => 'http://pear.php.net/dtd/rest.' .
                            $schema . ' http://pear.php.net/dtd/rest.' .
                            $schema . '.xsd',
                    ),
            ));
    }

    function getCategoryRESTLink($file)
    {
        return $this->uri . 'c/' . $file;
    }

    function getPackageRESTLink($file)
    {
        return $this->uri . 'p/' . $file;
    }

    function getReleaseRESTLink($file)
    {
        return $this->uri . 'r/' . $file;
    }

    function getMaintainerRESTLink($file)
    {
        return $this->uri . 'm/' . $file;
    }

    function getRESTPath($type, $file)
    {
        return $this->rest . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR .
            $file;
    }

    private function _initDir($dir, $dirname = false)
    {
        if (!$dirname) $dir = dirname($dir);
        if (!file_exists($dir)) {
            if (!@mkdir($dir, 0777, true)) {
                throw new PEAR2_SimpleChannelServer_Exception('Could not initialize' .
                    'REST category storage directory "' . $dir . '"');
            }
        }
    }

    private function _saveREST($path, $contents, $isxml, $type)
    {
        $this->_initDir($this->rest . '/' . $type . '/' . $path);
        if ($isxml) {
            $contents = (string) new \pear2\Pyrus\XMLWriter($contents);
        }
        file_put_contents($this->rest . '/' . $type . '/' . $path, $contents);
        chmod($this->rest . '/' . $type . '/' . $path, 0666);
    }

    function saveReleaseREST($path, $contents, $isxml = true)
    {
        $this->_saveREST($path, $contents, $isxml, 'r');
    }

    function saveCategoryREST($path, $contents, $isxml = true)
    {
        $this->_saveREST($path, $contents, $isxml, 'c');
    }

    function savePackageREST($path, $contents, $isxml = true)
    {
        $this->_saveREST($path, $contents, $isxml, 'p');
    }

    function saveMaintainerREST($path, $contents, $isxml = true)
    {
        $this->_saveREST($path, $contents, $isxml, 'm');
    }
}