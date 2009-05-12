<?php
/**
 * PEAR2_Pyrus_Package_Creator
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
 * Create packages using provided renderers.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Package_Creator
{
    const VERSION = '@PACKAGE_VERSION@';
    private $_creators;
    private $_handles = array();
    /**
     * Begin package creation
     *
     * @param array|PEAR2_Pyrus_Package_ICreator $creators
     */
    function __construct($creators, $pear2ExceptionPath = false, $pear2AutoloadPath = false,
                         $pear2MultiErrorsPath = false)
    {
        if (!$pear2ExceptionPath) {
            if (!($pear2Exception = @fopen('PEAR2/Exception.php', 'r', true))) {
                throw new PEAR2_Pyrus_Package_Exception('Cannot locate PEAR2/Exception.php, please' .
                    ' pass in the path to the constructor');
            }
        } else {
            if (!($pear2Exception = @fopen($pear2ExceptionPath, 'r', true))) {
                throw new PEAR2_Pyrus_Package_Exception('Cannot locate PEAR2/Exception.php' .
                    ' in ' . $pear2ExceptionPath);
            }
        }

        if (!$pear2AutoloadPath) {
            if (!($pear2Autoload = @fopen('PEAR2/Autoload.php', 'r', true))) {
                fclose($pear2Exception);
                throw new PEAR2_Pyrus_Package_Exception('Cannot locate PEAR2/Autoload.php, please' .
                    ' pass in the path to the constructor');
            }
        } else {
            if (!($pear2Autoload = @fopen($pear2AutoloadPath, 'r', true))) {
                fclose($pear2Exception);
                if ($a = realpath($pear2AutoloadPath)) {
                    $pear2AutoloadPath = $a;
                }
                throw new PEAR2_Pyrus_Package_Exception('Cannot locate PEAR2/Autoload.php' .
                    ' in ' . $pear2AutoloadPath);
            }
        }

        if (!$pear2MultiErrorsPath) {
            if (!($pear2MultiErrors = @fopen('PEAR2/MultiErrors.php', 'r', true))) {
                fclose($pear2Exception);
                fclose($pear2Autoload);
                throw new PEAR2_Pyrus_Package_Exception('Cannot locate PEAR2/MultiErrors.php, please' .
                    ' pass in the path to the constructor');
            }

            if (!($pear2MultiErrorsException = @fopen('PEAR2/MultiErrors/Exception.php', 'r', true))) {
                fclose($pear2Exception);
                fclose($pear2Autoload);
                fclose($pear2MultiErrors);
                throw new PEAR2_Pyrus_Package_Exception('Cannot locate PEAR2/MultiErrors/Exception.php, please' .
                    ' pass in the path to the constructor');
            }
        } else {
            if (dirname($pear2MultiErrorsPath) == dirname($pear2MultiErrorsPath . 'test')) {
                $pear2MultiErrorsPath .= '/';
            }

            if (!($pear2MultiErrors = @fopen($pear2MultiErrorsPath . 'MultiErrors.php', 'r', true))) {
                fclose($pear2Exception);
                fclose($pear2Autoload);
                throw new PEAR2_Pyrus_Package_Exception('Cannot locate PEAR2/MultiErrors.php' .
                    ' in ' . $pear2MultiErrorsPath . 'MultiErrors.php');
            }

            if (!($pear2MultiErrorsException = @fopen($pear2MultiErrorsPath . 'MultiErrors/Exception.php', 'r', true))) {
                fclose($pear2Exception);
                fclose($pear2Autoload);
                fclose($pear2MultiErrors);
                throw new PEAR2_Pyrus_Package_Exception('Cannot locate PEAR2/MultiErrors/Exception.php' .
                    ' in ' . $pear2MultiErrorsPath . 'MultiErrors/Exception.php');
            }
        }

        $this->_handles['src/PEAR2/Autoload.php'] = $pear2Autoload;
        $this->_handles['src/PEAR2/MultiErrors.php'] = $pear2MultiErrors;
        $this->_handles['src/PEAR2/MultiErrors/Exception.php'] = $pear2MultiErrorsException;
        $this->_handles['src/PEAR2/Exception.php'] = $pear2Exception;
        if ($creators instanceof PEAR2_Pyrus_Package_ICreator) {
            $this->_creators = array($creators);
        } elseif (is_array($creators)) {
            foreach ($creators as $creator) {
                if ($creator instanceof PEAR2_Pyrus_Package_ICreator) {
                    continue;
                }

                throw new PEAR2_Pyrus_Package_Creator_Exception('Invalid ' .
                    'PEAR2 package creator passed into PEAR2_Pyrus_Package_Creator');
            }
            $this->_creators = $creators;
        } else {
            throw new PEAR2_Pyrus_Package_Creator_Exception('Invalid ' .
                'PEAR2 package creator passed into PEAR2_Pyrus_Package_Creator');
        }
    }

    /**
     * Render packages from the creators passed into the constructor.
     *
     * This will take any package source and an array mapping internal
     * path => file name and create new packages in the formats requested.
     *
     * All files in package.xml will have the string @PACKAGE_VERSION@
     * automatically replaced with the current package's version
     * @param PEAR2_Pyrus_Package $package
     * @param array $extrafiles
     */
    function render(PEAR2_Pyrus_Package $package, array $extrafiles = array())
    {
        foreach ($this->_creators as $creator) {
            $creator->init();
        }

        $packagexml = '.xmlregistry/packages/' .
            str_replace('/', '!', $package->channel) . '/' . $package->name . '/' .
            $package->version['release'] . '-package.xml';
        if (self::VERSION === '@' . 'PACKAGE_VERSION@') {
            // we're running straight from SVN, so pretend to be 2.0.0
            $package->packagerversion = '2.0.0';
        } else {
            $package->packagerversion = self::VERSION;
        }

        // get packaging package.xml
        $packageingstr = (string) new PEAR2_Pyrus_XMLWriter($package->toArray(true));
        foreach ($this->_creators as $creator) {
            $creator->addFile($packagexml, $packageingstr);
        }

        // $packageat is the relative path within the archive
        // $info is an array of format:
        // array('attribs' => array('name' => ...)[, 'tasks:blah' ...])
        $alreadyPackaged = array();
        foreach ($package->packagingcontents as $packageat => $info) {
            $packageat = str_replace('\\', '/', $packageat);
            $packageat = str_replace('//', '/', $packageat);
            if ($packageat[0] === '/' ||
                  (strlen($packageat) > 2 && ($packageat[1] === ':' && $packageat[2] == '/'))) {
                throw new PEAR2_Pyrus_Package_Creator_Exception('Invalid path, cannot' .
                    ' save a root path ' . $packageat);
            }

            if (preg_match('@^\.\.?/|/\.\.?\\z|/\.\./@', $packageat)) {
                throw new PEAR2_Pyrus_Package_Creator_Exception('Invalid path, cannot' .
                    ' use directory reference . or .. ' . $packageat);
            }

            $alreadyPackaged[$packageat] = true;
            $contents = $package->getFileContents($info['attribs']['name']);
            $globalreplace = array('attribs' =>
                        array('from' => '@' . 'PACKAGE_VERSION@',
                              'to' => 'version',
                              'type' => 'package-info'));
            if (isset($info['tasks:replace'])) {
                if (isset($info['tasks:replace'][0])) {
                    $info['tasks:replace'][] = $globalreplace;
                } else {
                    $info['tasks:replace'] = array($info['tasks:replace'], $globalreplace);
                }
            } else {
                $info['tasks:replace'] = $globalreplace;
            }

            foreach (new PEAR2_Pyrus_Package_Creator_TaskIterator($info, $package) as $task) {
                // do pre-processing of file contents
                try {
                    // TODO: get last installed version into that last "null"
                    $task[1]->init($task[0], $info['attribs'], null);
                    $newcontents = $task[1]->startSession($package, $contents, $packageat);
                    if ($newcontents) {
                        $contents = $newcontents;
                    }
                } catch (Exception $e) {
                    // TODO: handle exceptions
                }
            }

            foreach ($this->_creators as $creator) {
                $creator->mkdir(dirname($packageat));
                $creator->addFile($packageat, $contents);
            }
        }

        $creator->mkdir('src/PEAR2');
        foreach ($this->_handles as $path => $stream) {
            if (isset($alreadyPackaged[$path])) {
                continue; // we're packaging this package
            }

            foreach ($this->_creators as $creator) {
                $creator->addFile($path, $stream);
            }

            fclose($stream);
        }

        foreach ($this->_creators as $creator) {
            if (isset($alreadyPackaged['src/PEAR2/MultiErrors/Exception.php'])) {
                continue; // we're packaging MultiErrors package
            }

            $creator->mkdir('src/PEAR2/MultiErrors');
            $creator->addFile('src/PEAR2/MultiErrors/Exception.php',
                "<?php\nclass PEAR2_MultiErrors_Exception extends PEAR2_Exception {}");
        }

        foreach ($extrafiles as $path => $filename) {
            $path = str_replace('\\', '/', $path);
            $path = str_replace('//', '/', $path);
            if ($path[0] === '/' ||
                  (strlen($path) > 2 && ($path[1] === ':' && $path[2] == '/'))) {
                throw new PEAR2_Pyrus_Package_Creator_Exception('Invalid path, cannot' .
                    ' save a root path ' . $path);
            }

            if (preg_match('@^\.\.?/|/\.\.?\\z|/\.\./@', $path)) {
                throw new PEAR2_Pyrus_Package_Creator_Exception('Invalid path, cannot' .
                    ' use directory reference . or .. ' . $path);
            }

            if (isset($alreadyPackaged[$path])) {
                throw new PEAR2_Pyrus_Package_Creator_Exception('Path ' . $path .
                    'has already been added, and cannot be overwritten');
            }

            $alreadyPackaged[$path] = true;
            if (!@file_exists($filename) || !($fp = @fopen($filename, 'rb'))) {
                throw new PEAR2_Pyrus_Package_Creator_Exception('Extra file ' .
                    $filename . ' does not exist or cannot be read');
            }

            foreach ($this->_creators as $creator) {
                $creator->mkdir(dirname($path));
                $creator->addFile($path, $fp);
            }

            fclose($fp);
        }

        foreach ($this->_creators as $creator) {
            $creator->close();
        }
    }
}