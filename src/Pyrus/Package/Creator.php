<?php
/**
 * \pear2\Pyrus\Package\Creator
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
namespace pear2\Pyrus\Package;
class Creator
{
    const VERSION = '@PACKAGE_VERSION@';
    private $_creators;
    private $_handles = array();
    protected $prepend;
    /**
     * Begin package creation
     *
     * @param array|\pear2\Pyrus\Package\ICreator $creators
     */
    function __construct($creators, $pear2ExceptionPath = false, $pear2AutoloadPath = false,
                         $pear2MultiErrorsPath = false)
    {
        if (!$pear2ExceptionPath) {
            if (!($pear2Exception = @fopen('PEAR2/Exception.php', 'r', true))) {
                throw new \pear2\Pyrus\Package\Exception('Cannot locate PEAR2/Exception.php, please' .
                    ' pass in the path to the constructor');
            }
        } else {
            if ($a = realpath($pear2ExceptionPath)) {
                $pear2ExceptionPath = $a;
            }
            if (dirname($pear2ExceptionPath) == dirname($pear2ExceptionPath . 'test')) {
                $pear2ExceptionPath .= '/';
            }
            if (!($pear2Exception = @fopen($pear2ExceptionPath . 'Exception.php', 'r'))) {
                throw new \pear2\Pyrus\Package\Exception('Cannot locate PEAR2/Exception.php' .
                    ' in ' . $pear2ExceptionPath);
            }
        }

        if (!$pear2AutoloadPath) {
            if (!($pear2Autoload = @fopen('PEAR2/Autoload.php', 'r', true))) {
                fclose($pear2Exception);
                throw new \pear2\Pyrus\Package\Exception('Cannot locate PEAR2/Autoload.php, please' .
                    ' pass in the path to the constructor');
            }
        } else {
            if ($a = realpath($pear2AutoloadPath)) {
                $pear2AutoloadPath = $a;
            }
            if (dirname($pear2AutoloadPath) == dirname($pear2AutoloadPath . 'test')) {
                $pear2AutoloadPath .= '/';
            }
            if (!($pear2Autoload = @fopen($pear2AutoloadPath . 'Autoload.php', 'r'))) {
                fclose($pear2Exception);
                throw new \pear2\Pyrus\Package\Exception('Cannot locate PEAR2/Autoload.php' .
                    ' in ' . $pear2AutoloadPath);
            }
        }

        if (!$pear2MultiErrorsPath) {
            if (!($pear2MultiErrors = @fopen('PEAR2/MultiErrors.php', 'r', true))) {
                fclose($pear2Exception);
                fclose($pear2Autoload);
                throw new \pear2\Pyrus\Package\Exception('Cannot locate PEAR2/MultiErrors.php, please' .
                    ' pass in the path to the constructor');
            }

            if (!($pear2MultiErrorsException = @fopen('PEAR2/MultiErrors/Exception.php', 'r', true))) {
                fclose($pear2Exception);
                fclose($pear2Autoload);
                fclose($pear2MultiErrors);
                throw new \pear2\Pyrus\Package\Exception('Cannot locate PEAR2/MultiErrors/Exception.php, please' .
                    ' pass in the path to the constructor');
            }
        } else {
            if ($a = realpath($pear2MultiErrorsPath)) {
                $pear2MultiErrorsPath = $a;
            }
            if (dirname($pear2MultiErrorsPath) == dirname($pear2MultiErrorsPath . 'test')) {
                $pear2MultiErrorsPath .= '/';
            }

            if (!($pear2MultiErrors = @fopen($pear2MultiErrorsPath . 'MultiErrors.php', 'r'))) {
                fclose($pear2Exception);
                fclose($pear2Autoload);
                throw new \pear2\Pyrus\Package\Exception('Cannot locate PEAR2/MultiErrors.php' .
                    ' in ' . $pear2MultiErrorsPath . 'MultiErrors.php');
            }

            if (!($pear2MultiErrorsException = @fopen($pear2MultiErrorsPath . 'MultiErrors/Exception.php', 'r'))) {
                fclose($pear2Exception);
                fclose($pear2Autoload);
                fclose($pear2MultiErrors);
                throw new \pear2\Pyrus\Package\Exception('Cannot locate PEAR2/MultiErrors/Exception.php' .
                    ' in ' . $pear2MultiErrorsPath . 'MultiErrors/Exception.php');
            }
        }

        $this->_handles['php/PEAR2/Autoload.php'] = $pear2Autoload;
        $this->_handles['php/PEAR2/MultiErrors.php'] = $pear2MultiErrors;
        $this->_handles['php/PEAR2/MultiErrors/Exception.php'] = $pear2MultiErrorsException;
        $this->_handles['php/PEAR2/Exception.php'] = $pear2Exception;
        if ($creators instanceof \pear2\Pyrus\Package\ICreator) {
            $this->_creators = array($creators);
        } elseif (is_array($creators)) {
            foreach ($creators as $creator) {
                if ($creator instanceof \pear2\Pyrus\Package\ICreator) {
                    continue;
                }

                throw new \pear2\Pyrus\Package\Creator\Exception('Invalid ' .
                    'PEAR2 package creator passed into \pear2\Pyrus\Package\Creator');
            }
            $this->_creators = $creators;
        } else {
            throw new \pear2\Pyrus\Package\Creator\Exception('Invalid ' .
                'PEAR2 package creator passed into \pear2\Pyrus\Package\Creator');
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
     * @param \pear2\Pyrus\Package $package
     * @param array $extrafiles
     */
    function render(\pear2\Pyrus\Package $package, array $extrafiles = array())
    {
        foreach ($this->_creators as $creator) {
            $creator->init();
        }

        $this->prepend = $prepend = $package->name . '-' . $package->version['release'];
        if ($package->isNewPackage()) {
            $packagexml = $prepend . '/.xmlregistry/packages/' .
                str_replace('/', '!', $package->channel) . '/' . $package->name . '/' .
                $package->version['release'] . '-info.xml';
        } else {
            if ($package->isOldAndCrustyCompatible()) {
                $packagexml = 'package2.xml';
                $old = file_get_contents('package.xml');
            } else {
                $packagexml = 'package.xml';
            }
        }
        if (self::VERSION === '@' . 'PACKAGE_VERSION@') {
            // we're running straight from SVN, so pretend to be 2.0.0
            $package->packagerversion = '2.0.0';
        } else {
            $package->packagerversion = self::VERSION;
        }

        // get packaging package.xml
        $packageingstr = (string) new \pear2\Pyrus\XMLWriter($package->toArray(true));
        foreach ($this->_creators as $creator) {
            $creator->addFile($packagexml, $packageingstr);
        }
        if ($package->isOldAndCrustyCompatible()) {
            foreach ($this->_creators as $creator) {
                $creator->addFile('package.xml', $old);
            }
        }
        if ($package->getInternalPackage() instanceof \pear2\Pyrus\Package\Xml) {
            // check for package_compatible.xml
            if ($package->isNewPackage() && file_exists($package->getFilePath('package_compatible.xml'))) {
                foreach ($this->_creators as $creator) {
                    $creator->addFile('package.xml',
                                      file_get_contents($package->getFilePath('package_compatible.xml')));
                }
            }
        }
        $packagingloc = \pear2\Pyrus\Config::current()->temp_dir . DIRECTORY_SEPARATOR . 'pyrpackage';
        if (file_exists($packagingloc)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($packagingloc,
                        \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
                if (is_dir($file)) {
                    rmdir($file);
                } elseif (is_file($file)) {
                    unlink($file);
                }
            }
        } else {
            mkdir($packagingloc, 0777, true);
        }

        // $packageat is the relative path within the archive
        // $info is an array of format:
        // array('attribs' => array('name' => ...)[, 'tasks:blah' ...])
        $alreadyPackaged = array();
        $globalreplace = array('attribs' =>
                    array('from' => '@' . 'PACKAGE_VERSION@',
                          'to' => 'version',
                          'type' => 'package-info'));
        foreach ($package->packagingcontents as $packageat => $info) {
            $role =
                \pear2\Pyrus\Installer\Role::factory($package->getPackageType(), $info['attribs']['role']);
            try {
                $role->packageTimeValidate($package, $info);
            } catch (\Exception $e) {
                throw new \pear2\Pyrus\Package\Creator\Exception('Invalid file ' .
                            $packageat . ': ' . $e->getMessage(), $e);
            }

            $packageat = str_replace('\\', '/', $packageat);
            $packageat = str_replace('//', '/', $packageat);
            if ($packageat[0] === '/' ||
                  (strlen($packageat) > 2 && ($packageat[1] === ':' && $packageat[2] == '/'))) {
                throw new \pear2\Pyrus\Package\Creator\Exception('Invalid path, cannot' .
                    ' save a root path ' . $packageat);
            }

            if (preg_match('@^\.\.?/|/\.\.?\\z|/\.\./@', $packageat)) {
                throw new \pear2\Pyrus\Package\Creator\Exception('Invalid path, cannot' .
                    ' use directory reference . or .. ' . $packageat);
            }

            $alreadyPackaged[$packageat] = true;
            $packageat = $prepend . '/' . $packageat;
            $contents = $package->getFileContents($info['attribs']['name'], true);
            if (!file_exists(dirname($packagingloc . DIRECTORY_SEPARATOR . $packageat))) {
                mkdir(dirname($packagingloc . DIRECTORY_SEPARATOR . $packageat), 0777, true);
            }
            $fp = fopen($packagingloc . DIRECTORY_SEPARATOR . $packageat, 'wb+');
            ftruncate($fp, 0);
            stream_copy_to_stream($contents, $fp);
            fclose($contents);
            rewind($fp);
            if ($package->isNewPackage() && $info['attribs']['role'] == 'php') {
                if (isset($info['tasks:replace'])) {
                    if (isset($info['tasks:replace'][0])) {
                        $info['tasks:replace'][] = $globalreplace;
                    } else {
                        $info['tasks:replace'] = array($info['tasks:replace'], $globalreplace);
                    }
                } else {
                    $info['tasks:replace'] = $globalreplace;
                }
            }

            if (isset(\pear2\Pyrus\Config::current()->registry->package[$package->channel . '/' . $package->name])) {
                $version = \pear2\Pyrus\Config::current()->registry->info($package->name, $package->channel, 'version');
            } else {
                $version = null;
            }
            foreach (new \pear2\Pyrus\Package\Creator\TaskIterator($info, $package,
                                                                  \pear2\Pyrus\Task\Common::PACKAGE,
                                                                  $version) as $task) {
                // do pre-processing of file contents
                try {
                    $task->startSession($fp, $packageat);
                } catch (\Exception $e) {
                    // TODO: handle exceptions
                }
            }
            fclose($fp);
        }

        foreach ($this->_creators as $creator) {
            $creator->addDir($packagingloc);
        }

        if ($package->isNewPackage()) {
            $this->addPEAR2Stuff($alreadyPackaged);
        }
        $this->addExtraFiles($extrafiles);

        foreach ($this->_creators as $creator) {
            $creator->close();
        }
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($packagingloc,
                    \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if (is_dir($file)) {
                rmdir($file);
            } elseif (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($packagingloc);
    }

    protected function addPEAR2Stuff($alreadyPackaged)
    {
        foreach ($this->_creators as $creator) {
            $creator->mkdir($this->prepend . '/php/PEAR2');
        }
        foreach ($this->_handles as $path => $stream) {
            if (isset($alreadyPackaged[$path])) {
                continue; // we're packaging this package
            }

            foreach ($this->_creators as $creator) {
                $creator->addFile($this->prepend . '/' . $path, $stream);
            }

            fclose($stream);
        }

        foreach ($this->_creators as $creator) {
            if (isset($alreadyPackaged['php/PEAR2/MultiErrors/Exception.php'])) {
                continue; // we're packaging MultiErrors package
            }

            $creator->mkdir($this->prepend . '/php/PEAR2/MultiErrors');
            $creator->addFile($this->prepend . '/php/PEAR2/MultiErrors/Exception.php',
                "<?php\nclass PEAR2_MultiErrors_Exception extends \PEAR2_Exception {}");
        }
    }

    function addExtraFiles($extrafiles)
    {
        foreach ($extrafiles as $path => $filename) {
            if (is_object($filename)) {
                if ($filename instanceof \pear2\Pyrus\Package) {
                    foreach ($filename->packagingcontents as $path => $info) {
                        foreach ($this->_creators as $creator) {
                            $creator->mkdir(dirname($this->prepend . '/' . $path));
                            $fp = $filename->getFileContents($info['attribs']['name'], true);
                            $creator->addFile($this->prepend . '/' . $path, $fp);
                            fclose($fp);
                        }
                    }
                    continue;
                } else {
                    throw new Exception('Invalid extra file object, must be ' .
                                        'a \pear2\Pyrus\Package object');
                }
            }
            $path = str_replace('\\', '/', $path);
            $path = str_replace('//', '/', $path);
            if ($path[0] === '/' ||
                  (strlen($path) > 2 && ($path[1] === ':' && $path[2] == '/'))) {
                throw new \pear2\Pyrus\Package\Creator\Exception('Invalid path, cannot' .
                    ' save a root path ' . $path);
            }

            if (preg_match('@^\.\.?/|/\.\.?\\z|/\.\./@', $path)) {
                throw new \pear2\Pyrus\Package\Creator\Exception('Invalid path, cannot' .
                    ' use directory reference . or .. ' . $path);
            }

            if (isset($alreadyPackaged[$path])) {
                throw new \pear2\Pyrus\Package\Creator\Exception('Path ' . $path .
                    'has already been added, and cannot be overwritten');
            }

            $alreadyPackaged[$path] = true;
            if (!@file_exists($filename) || !($fp = @fopen($filename, 'rb'))) {
                throw new \pear2\Pyrus\Package\Creator\Exception('Extra file ' .
                    $filename . ' does not exist or cannot be read');
            }

            foreach ($this->_creators as $creator) {
                $creator->mkdir(dirname($this->prepend . '/' . $path));
                $creator->addFile($this->prepend . '/' . $path, $fp);
            }

            fclose($fp);
        }
    }
}