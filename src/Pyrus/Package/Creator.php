<?php
/**
 * \Pyrus\Package\Creator
 *
 * PHP version 5.3
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * Create packages using provided renderers.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\Package;
class Creator
{
    const VERSION = '@PACKAGE_VERSION@';
    private $_creators;
    private $_handles = array();
    protected $prepend;

    /**
     * Begin package creation
     *
     * @param array|\Pyrus\Package\CreatorInterface $creators
     */
    function __construct($creators, $pear2ExceptionPath = false, $pear2AutoloadPath = false,
                         $pear2MultiErrorsPath = false)
    {
        $this->setCreators($creators)
            ->bundleMinimumDeps($pear2ExceptionPath, $pear2AutoloadPath, $pear2MultiErrorsPath);
    }

    /**
     * Set/inject creators.
     *
     * @param mixed $creators CreatorInterface or an array of CreatorInterface.
     *
     * @return $this
     * @throws Creator\Exception On invalid argument.
     */
    public function setCreators($creators)
    {
        if ($creators instanceof CreatorInterface) {
            $this->_creators = array($creators);
            return $this;
        }

        if (is_array($creators)) {
            foreach ($creators as $creator) {
                if ($creator instanceof CreatorInterface) {
                    continue;
                }
                throw new Creator\Exception('Invalid PEAR2 package creator passed into \Pyrus\Package\Creator');
            }
            $this->_creators = $creators;
            return $this;
        }
        throw new Creator\Exception('Invalid PEAR2 package creator passed into \Pyrus\Package\Creator');
    }

    /**
     * Bundle the minimum required dependencies for a package.
     *
     * @param boolean $pear2ExceptionPath
     * @param boolean $pear2AutoloadPath
     * @param boolean $pear2MultiErrorsPath
     *
     * @return void
     */
    protected function bundleMinimumDeps(
        $pear2ExceptionPath = false,
        $pear2AutoloadPath = false,
        $pear2MultiErrorsPath = false
    ) {
/*
        if (!$pear2ExceptionPath) {
            if (!($pear2Exception = @fopen('PEAR2/Exception.php', 'rb', true))) {
                throw new Exception('Cannot locate PEAR2/Exception.php, please' .
                                    ' pass in the path to the constructor');
            }
        } else {
            if ($a = realpath($pear2ExceptionPath)) {
                $pear2ExceptionPath = $a;
            }

            if (dirname($pear2ExceptionPath) == dirname($pear2ExceptionPath . 'test')) {
                $pear2ExceptionPath .= '/';
            }

            if (!($pear2Exception = @fopen($pear2ExceptionPath . 'Exception.php', 'rb'))) {
                throw new Exception('Cannot locate PEAR2/Exception.php in ' . $pear2ExceptionPath);
            }
        }
*/

        if (!$pear2AutoloadPath) {
            if (!($pear2Autoload = @fopen('PEAR2/Autoload.php', 'rb', true))) {
                //fclose($pear2Exception);
                throw new Exception('Cannot locate PEAR2/Autoload.php, please' .
                                    ' pass in the path to the constructor');
            }
        } else {
            if ($a = realpath($pear2AutoloadPath)) {
                $pear2AutoloadPath = $a;
            }

            if (dirname($pear2AutoloadPath) == dirname($pear2AutoloadPath . 'test')) {
                $pear2AutoloadPath .= '/';
            }

            if (!($pear2Autoload = @fopen($pear2AutoloadPath . 'Autoload.php', 'rb'))) {
                fclose($pear2Exception);
                throw new Exception('Cannot locate PEAR2/Autoload.php in ' . $pear2AutoloadPath);
            }
        }

/*
        if (!$pear2MultiErrorsPath) {
            if (!($pear2MultiErrors = @fopen('PEAR2/MultiErrors.php', 'rb', true))) {
                fclose($pear2Exception);
                fclose($pear2Autoload);
                throw new Exception('Cannot locate PEAR2/MultiErrors.php, please' .
                                    ' pass in the path to the constructor');
            }

            if (!($pear2MultiErrorsException = @fopen('PEAR2/MultiErrors/Exception.php', 'rb', true))) {
                fclose($pear2Exception);
                fclose($pear2Autoload);
                fclose($pear2MultiErrors);
                throw new Exception('Cannot locate PEAR2/MultiErrors/Exception.php, please' .
                                    ' pass in the path to the constructor');
            }
        } else {
            if ($a = realpath($pear2MultiErrorsPath)) {
                $pear2MultiErrorsPath = $a;
            }

            if (dirname($pear2MultiErrorsPath) == dirname($pear2MultiErrorsPath . 'test')) {
                $pear2MultiErrorsPath .= '/';
            }

            if (!($pear2MultiErrors = @fopen($pear2MultiErrorsPath . 'MultiErrors.php', 'rb'))) {
                fclose($pear2Exception);
                fclose($pear2Autoload);
                throw new Exception('Cannot locate PEAR2/MultiErrors.php in ' . $pear2MultiErrorsPath . 'MultiErrors.php');
            }

            if (!($pear2MultiErrorsException = @fopen($pear2MultiErrorsPath . 'MultiErrors/Exception.php', 'rb'))) {
                fclose($pear2Exception);
                fclose($pear2Autoload);
                fclose($pear2MultiErrors);
                throw new Exception('Cannot locate PEAR2/MultiErrors/Exception.php' .
                                    ' in ' . $pear2MultiErrorsPath . 'MultiErrors/Exception.php');
            }
        }
*/

        $this->_handles['php/PEAR2/Autoload.php'] = $pear2Autoload;
        //$this->_handles['php/PEAR2/MultiErrors.php'] = $pear2MultiErrors;
        //$this->_handles['php/PEAR2/MultiErrors/Exception.php'] = $pear2MultiErrorsException;
        //$this->_handles['php/PEAR2/Exception.php'] = $pear2Exception;
    }

    /**
     * Render packages from the creators passed into the constructor.
     *
     * This will take any package source and an array mapping internal
     * path => file name and create new packages in the formats requested.
     *
     * All files in package.xml will have the string @PACKAGE_VERSION@
     * automatically replaced with the current package's version
     * @param \Pyrus\Package $package
     * @param array $extrafiles
     */
    function render(\Pyrus\Package $package, array $extrafiles = array())
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
        $packageingstr = (string) new \Pyrus\XMLWriter($package->toArray(true));
        foreach ($this->_creators as $creator) {
            $creator->addFile($packagexml, $packageingstr);
        }

        if ($package->isOldAndCrustyCompatible()) {
            foreach ($this->_creators as $creator) {
                $creator->addFile('package.xml', $old);
            }
        }

        if ($package->getInternalPackage() instanceof Xml) {
            // check for package_compatible.xml
            if ($package->isNewPackage() && file_exists($package->getFilePath('package_compatible.xml'))) {
                foreach ($this->_creators as $creator) {
                    $creator->addFile('package.xml',
                                      file_get_contents($package->getFilePath('package_compatible.xml')));
                }
            }
        }

        $packagingloc = \Pyrus\Config::current()->temp_dir . DIRECTORY_SEPARATOR . 'pyrpackage';
        if (file_exists($packagingloc)) {
            \Pyrus\Filesystem::rmrf($packagingloc, false, false);
        }
        mkdir($packagingloc, 0777, true);

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
                \Pyrus\Installer\Role::factory($package->getPackageType(), $info['attribs']['role']);
            try {
                $role->packageTimeValidate($package, $info);
            } catch (\Exception $e) {
                throw new Creator\Exception('Invalid file ' . $packageat . ': ' . $e->getMessage(), $e);
            }

            $packageat = str_replace('\\', '/', $packageat);
            $packageat = str_replace('//', '/', $packageat);
            if ($packageat[0] === '/' ||
                  (strlen($packageat) > 2 && ($packageat[1] === ':' && $packageat[2] == '/'))) {
                throw new Creator\Exception('Invalid path, cannot save a root path ' . $packageat);
            }

            if (preg_match('@^\.\.?/|/\.\.?\\z|/\.\./@', $packageat)) {
                throw new Creator\Exception('Invalid path, cannot use directory ' .
                                            'reference . or .. ' . $packageat);
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

            if (isset(\Pyrus\Config::current()->registry->package[$package->channel . '/' . $package->name])) {
                $version = \Pyrus\Config::current()->registry->info($package->name, $package->channel, 'version');
            } else {
                $version = null;
            }

            foreach (new Creator\TaskIterator($info, $package, \Pyrus\Task\Common::PACKAGE,
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
        \Pyrus\Filesystem::rmrf($packagingloc, false, false);
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
                "<?php\nnamespace PEAR2\MultiErrors; class Exception extends \PEAR2\Exception {}");
        }
    }

    function addExtraFiles($extrafiles)
    {
        foreach ($extrafiles as $path => $filename) {
            if (is_object($filename)) {
                if ($filename instanceof \Pyrus\PackageInterface) {
                    foreach ($filename->packagingcontents as $info) {
                        foreach ($this->_creators as $creator) {
                            $creator->mkdir(dirname($this->prepend . '/' . $info['attribs']['name']));
                            $fp = $filename->getFileContents($info['attribs']['name'], true);
                            $creator->addFile($this->prepend . '/' . $info['attribs']['name'], $fp);
                            fclose($fp);
                        }
                    }
                    continue;
                }

                throw new Exception('Invalid extra file object, must be ' .
                                        'a \Pyrus\Package object');
            }

            // $extrafiles may also contain string => string entries.
            $path = str_replace(array('\\', '//'), '/', $path);
            if ($path[0] === '/' ||
                  (strlen($path) > 2 && ($path[1] === ':' && $path[2] == '/'))) {
                throw new Creator\Exception('Invalid path, cannot save a root path ' . $path);
            }

            if (preg_match('@^\.\.?/|/\.\.?\\z|/\.\./@', $path)) {
                throw new Creator\Exception('Invalid path, cannot use directory ' .
                                            'reference . or .. ' . $path);
            }

            if (isset($alreadyPackaged[$path])) {
                throw new Creator\Exception('Path ' . $path . 'has already been added, and cannot be overwritten');
            }

            $alreadyPackaged[$path] = true;
            if (!@file_exists($filename) || !($fp = @fopen($filename, 'rb'))) {
                throw new Creator\Exception('Extra file ' . $filename . ' does not exist or cannot be read');
            }

            foreach ($this->_creators as $creator) {
                $creator->mkdir(dirname($this->prepend . '/' . $path));
                $creator->addFile($this->prepend . '/' . $path, $fp);
            }

            fclose($fp);
        }
    }
}
