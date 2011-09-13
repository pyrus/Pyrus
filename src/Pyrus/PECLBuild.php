<?php
/**
 * \Pyrus\PECLBuild
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

/**
 * Class handling building of PECL packages
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus;
class PECLBuild
{
    protected $ui;
    protected $buildDirectory;
    /* don't think we need this, but will reserve judgement until I hear from internals@
    protected $zend_extension_api_no = 1;
    protected $zend_module_api_no = 1;
    protected $php_api_version = 1;
    */

    function __construct($ui)
    {
        $this->ui = $ui;
    }

    function installBuiltStuff(Registry\Package\Base $pkg, array $built)
    {
        foreach ($built as $ext) {
            $info = pathinfo($ext['file']);
            if ($info['extension'] === 'so' || $info['extension'] === 'dll') {
                if (extension_loaded(basename($ext['file'], $info['extension']))) {
                    $this->raiseError("Extension '" . basename($ext['file'], $info['extension']) .
                                      "' already loaded. " .
                                      'Please unload it in your php.ini file ' .
                                      'prior to install or upgrade');
                }
                $role = 'ext';
            } else {
                $role = 'src';
            }

            $copyto = $dest = $ext['dest'];
            $packagingroot = '';
            if (isset(Main::$options['packagingroot'])) {
                $packagingroot = Main::$options['packagingroot'];
                $copyto = $this->_prependPath($dest, $packagingroot);
            }

            if ($copyto != $dest) {
                $this->log(1, "Installing '$dest' as '$copyto'");
            } else {
                $this->log(1, "Installing '$dest'");
            }

            $copydir = dirname($copyto);
            if (file_exists($copydir)) {
                if (!is_dir($copydir)) {
                    throw new PECLBuild\Exception('Cannot install extension, ' .
                                                              $copydir . ' exists and is a file (should be directory)');
                }
            } else {
                $oldmode = umask(Config::current()->umask);
                if (!mkdir($copydir, 0777, true)) {
                    umask($oldmode);
                    throw new PECLBuild\Exception("failed to mkdir $copydir");
                }
                umask($oldmode);

                $this->log(3, "+ mkdir $copydir");
            }

            // keep track of the user's track_errors setting
            $track_errors = ini_get('track_errors');

            ini_set('track_errors', true);
            if (!@copy($ext['file'], $copyto)) {
                throw new PECLBuild\Exception("failed to write $copyto ($php_errormsg)");
            }

            $oldmode = umask(Config::current()->umask);
            if (!@chmod($copyto, 0777)) {
                $this->log(0, "failed to change mode of $copyto ($php_errormsg)");
            }
            umask($oldmode);

            // restore track_errors
            ini_set('track_errors', $track_errors);

            $pkg->files[$ext['file']] = array('attribs' => array(
                'role' => $role,
                'name' => $ext['packagexml_name'],
                'installed_as' => $dest,
    /* don't think we need this, but will reserve judgement until I hear from internals@
                'php_api' => $ext['php_api'],
                'zend_mod_api' => $ext['zend_mod_api'],
                'zend_ext_api' => $ext['zend_ext_api'],
    */
                ));
        }
    }

    function listInstalledStuff($dir)
    {
        foreach (new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($dir,
                                                            0|\RecursiveDirectoryIterator::SKIP_DOTS),
                            \RecursiveIteratorIterator::SELF_FIRST) as $file) {
            $info = stat($file);
            $uname = posix_getpwuid($info['uid']);
            $uname = $uname['name'];
            $gname = posix_getgrgid($info['gid']);
            $gname = $gname['name'];
            $perms = fileperms($file);
            foreach (array('s' => 0xC000, 'l' => 0xA000, '-' => 0x8000, 'b' => 0x6000,
                           'd' => 0x4000, 'c' => 0x2000, 'p' => 0x1000)
                     as $letter => $mask) {
                if (($perms & $mask) === $mask) {
                    $perminfo = $letter;
                    break;
                }
            }
            if (!isset($perminfo)) {
                $perminfo = 'u';
            }

            // Owner
            $perminfo .= (($perms & 0x0100) ? 'r' : '-');
            $perminfo .= (($perms & 0x0080) ? 'w' : '-');
            $perminfo .= (($perms & 0x0040) ?
                        (($perms & 0x0800) ? 's' : 'x' ) :
                        (($perms & 0x0800) ? 'S' : '-'));

            // Group
            $perminfo .= (($perms & 0x0020) ? 'r' : '-');
            $perminfo .= (($perms & 0x0010) ? 'w' : '-');
            $perminfo .= (($perms & 0x0008) ?
                        (($perms & 0x0400) ? 's' : 'x' ) :
                        (($perms & 0x0400) ? 'S' : '-'));

            // World
            $perminfo .= (($perms & 0x0004) ? 'r' : '-');
            $perminfo .= (($perms & 0x0002) ? 'w' : '-');
            $perminfo .= (($perms & 0x0001) ?
                        (($perms & 0x0200) ? 't' : 'x' ) :
                        (($perms & 0x0200) ? 'T' : '-'));
            $date = new \DateTime;
            $date->setTimestamp($info['mtime']);

            echo $info['ino'], ' ', str_pad(($info['blocks']/2), 3, ' ', STR_PAD_LEFT), ' ', $perminfo, ' ',
                 $info['nlink'], ' ', $uname, ' ', $gname, ' ',
                 str_pad($info['size'], 10, ' ', STR_PAD_LEFT), ' ', $date->format('Y-m-d H:i'), ' ', $file, "\n";
        }
    }

    /**
     * @param string
     * @param string
     */
    protected function harvestInstDir($dest_prefix, $dirname, $instroot)
    {
        $defaultExtDir = Config::current()->defaultValue('ext_dir');
        $ext_dir = Config::current()->ext_dir;
        if ($ext_dir != $defaultExtDir) {
            $extReplace = $defaultExtDir;
        } else {
            $extReplace = '****';
        }

        $built_files = array();
        foreach (new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dirname,
                                                    0|\RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {

            $built_files[] = array(
                        'file' => (string) $file,
                        'dest' => str_replace(
                                    $extReplace,
                                    $ext_dir,
                                    $dest_prefix . '/' . str_replace($dirname . DIRECTORY_SEPARATOR, '', $file)),
                        'packagexml_name' => substr($file, strlen($dirname) + 1),
    /* don't think we need this, but will reserve judgement until I hear from internals@
                        'php_api' => $this->php_api_version,
                        'zend_mod_api' => $this->zend_module_api_no,
                        'zend_ext_api' => $this->zend_extension_api_no,
    */
                        );
        }
        return $built_files;
    }

    /**
     * Build an extension from source.  Runs "phpize" in the source
     * directory, and compiles there.
     *
     * @param \Pyrus\PackageInterface $pkg package object
     *
     * @param mixed $callback callback function used to report output,
     * see PEAR_Builder::runCommand for details
     *
     * @return array an array of associative arrays with built files,
     * format:
     * array( array( 'file' => '/path/to/ext.so',
     *               'php_api' => YYYYMMDD,
     *               'zend_mod_api' => YYYYMMDD,
     *               'zend_ext_api' => YYYYMMDD ),
     *        ... )
     *
     * @access public
     *
     * @see PEAR_Builder::runCommand
     */
    function build(Registry\Package\Base $pkg, $callback = null)
    {
        $config = Config::current();
        if (preg_match('/(\\/|\\\\|^)([^\\/\\\\]+)?php(.+)?$/',
                       $config->php_bin, $matches)) {
            if (isset($matches[2]) && strlen($matches[2]) &&
                trim($matches[2]) != trim($config->php_prefix)) {
                $this->log(0, 'WARNING: php_bin ' . $config->php_bin .
                           ' appears to have a prefix ' . $matches[2] . ', but' .
                           ' config variable php_prefix does not match');
            }
            if (isset($matches[3]) && strlen($matches[3]) &&
                trim($matches[3]) != trim($config->php_suffix)) {
                $this->log(0, 'WARNING: php_bin ' . $config->php_bin .
                           ' appears to have a suffix ' . $matches[3] . ', but' .
                           ' config variable php_suffix does not match');
            }
        }

        $this->current_callback = $callback;
        if ($pkg->isNewPackage()) {
            $dir = $config->src_dir . DIRECTORY_SEPARATOR .
                $pkg->channel . DIRECTORY_SEPARATOR . $pkg->name;
        } else {
            $dir = $config->src_dir . DIRECTORY_SEPARATOR . $pkg->name;
        }

        // Find config. outside of normal path - e.g. config.m4
        foreach ($pkg->installcontents as $file) {
            if (stristr(basename($file->name), 'config.m4')) {
                $dir .= DIRECTORY_SEPARATOR . dirname($file->name);
                break;
          }
        }

        $this->buildDirectory = $dir;
        $old_cwd = getcwd();
        if (!file_exists($dir) || !is_dir($dir) || !chdir($dir)) {
            throw new PECLBuild\Exception('could not chdir to package directory ' . $dir);
        }

        if (!is_writable($dir)) {
            throw new PECLBuild\Exception('cannot build in package directory ' . $dir .
                                                      ', directory not writable');
        }

        $path = $config->bin_dir;
        if ($env_path = getenv('PATH')) {
            $path .= ':' . $env_path;
        }

        $this->log(0, "cleaning build directory $dir");
        $this->runCommand($config->php_prefix
                                . "phpize" .
                                $config->php_suffix . ' --clean',
                                null,
                                array('PATH' => $path));

        $this->log(0, "building in $dir");
        if (!$this->runCommand($config->php_prefix
                                . "phpize" .
                                $config->php_suffix,
                                null, /*array($this, 'phpizeCallback'),*/
                                array('PATH' => $path))) {
            throw new PECLBuild\Exception('phpize failed - if running phpize manually from ' . $dir .
                                                      ' works, please open a bug for pyrus with details');
        }

        // {{{ start of interactive part
        $configure = "$dir/configure"
                           . " --with-php-config="
                           . $config->php_prefix
                           . "php-config"
                           . $config->php_suffix;
        if (count($pkg->installrelease->configureoption)) {
            foreach ($pkg->installrelease->configureoption as $o) {
                list($r) = $this->ui->ask($o->prompt, array(), $o->default);
                if (substr($o->name, 0, 5) == 'with-' &&
                    ($r == 'yes' || $r == 'autodetect')) {
                    $configure .= ' --' . $o->name;
                } else {
                    $configure .= ' --' . $o->name . '=' . trim($r);
                }
            }
        }
        // }}} end of interactive part

        $inst_dir = $dir . '/.install';
        $this->log(1, "building in $dir");
        if (!file_exists($inst_dir) && !mkdir($inst_dir, 0755, true) || !is_dir($inst_dir)) {
            throw new PECLBuild\Exception('could not create temporary install dir: ' . $inst_dir);
        }


        $make = getenv('MAKE') ? getenv('MAKE') : 'make';
        $to_run = array(
            $configure,
            $make,
            "$make INSTALL_ROOT=\"$inst_dir\" install",
            );
        if (!file_exists($dir) || !is_dir($dir) || !chdir($dir)) {
            throw new PECLBuild\Exception('could not chdir to ' . $dir);
        }

        $env = $_ENV;
        if (count($env) == 0) {
            //variables_order may not include E
            $env['PATH'] = getenv('PATH');
        }
        // this next line is modified by the installer at packaging time
        if ('@PEAR-VER@' == '@'.'PEAR-VER@') {
            // we're running from svn
            $env['PHP_PEAR_VERSION'] = '2.0.0a4';
        } else {
            $env['PHP_PEAR_VERSION'] = '@PEAR-VER@';
        }

        foreach ($to_run as $cmd) {
            try {
                if (!$this->runCommand($cmd, $callback, $env)) {
                    throw new PECLBuild\Exception("`$cmd' failed");
                }
            } catch (\Exception $e) {
                chdir($old_cwd);
                throw $e;
            }
        }

        $this->listInstalledStuff($inst_dir);

        if (!file_exists('modules') || !is_dir('modules')) {
            chdir($old_cwd);
            throw new PECLBuild\Exception("no `modules' directory found");
        }

        $built_files = array();
        $prefix = exec($config->php_prefix
                        . "php-config" .
                       $config->php_suffix . " --prefix");
        $built_files = $this->harvestInstDir($prefix, $inst_dir . DIRECTORY_SEPARATOR . $prefix, $inst_dir);

        chdir($old_cwd);
        return $built_files;
    }

    /**
     * Message callback function used when running the "phpize"
     * program.  Extracts the API numbers used.  Ignores other message
     * types than "cmdoutput".
     *
     * @param string $what the type of message
     * @param mixed $data the message
     *
     * @return void
     *
     * @access public
     */
    /* don't think we need this, but will reserve judgement until I hear from internals@
    function phpizeCallback($what, $data)
    {
        if ($what != 'cmdoutput') {
            return;
        }
        $this->log(1, rtrim($data));
        if (preg_match('/You should update your .aclocal.m4/', $data)) {
            return;
        }
        $matches = array();
        if (preg_match('/^((?:[a-zA-Z]+ ?)+):\s+(\d+)/', $data, $matches)) {
            $member = preg_replace('/[^a-z]/', '_', strtolower($matches[1]));
            $apino = (int)$matches[2];
            if (isset($this->$member)) {
                $this->$member = $apino;
            }
        }
    }*/

    /**
     * Run an external command, using a message callback to report
     * output.  The command will be run through popen and output is
     * reported for every line with a "cmdoutput" message with the
     * line string, including newlines, as payload.
     *
     * @param string $command the command to run
     *
     * @param mixed $callback (optional) function to use as message
     * callback
     *
     * @return bool whether the command was successful (exit code 0
     * means success, any other means failure)
     */
    private function runCommand($command, $callback = null, $env = null)
    {
        $this->log(1, "running: $command 2>&1");
        $exitcode = $this->system_with_timeout($command . ' 2>&1', $this->buildDirectory, $callback, $env);
        return ($exitcode == 0);
    }

    /**
     * Ported from PHP 5.3's run-tests.php
     */
    function system_with_timeout($commandline, $cwd, $callback = null, $env = null, $stdin = null, $timeout = 60)
    {
        $data = '';

        $bin_env = array();
        foreach((array)$env as $key => $value) {
            $bin_env[(binary)$key] = (binary)$value;
        }

        $proc = proc_open($commandline, array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
            ), $pipes, $cwd, $bin_env, array('suppress_errors' => true, 'binary_pipes' => true));

        if (!$proc) {
            return false;
        }

        if (!is_null($stdin)) {
            fwrite($pipes[0], (binary) $stdin);
        }
        fclose($pipes[0]);

        while (true) {
            /* hide errors from interrupted syscalls */
            $r = $pipes;
            $w = null;
            $e = null;

            $n = @stream_select($r, $w, $e, $timeout);

            if ($n === false) {
                break;
            } else if ($n === 0) {
                /* timed out */
                proc_terminate($proc, 9);
                throw new PECLBuild\Exception('Error: Process timed out');
            } else if ($n > 0) {
                $called = false;
                while ($line = fgets($pipes[1], 1024)) {
                    $called = true;
                    if ($callback) {
                        call_user_func($callback, 'cmdoutput', $line);
                    } else {
                        $this->log(0, rtrim($line));
                    }
                }
                if (!$called) {
                    break;
                }
            }
        }

        $stat = proc_get_status($proc);

        if ($stat['signaled']) {
            $code = proc_close($proc);
            throw new PECLBuild\Exception('Process was stopped with a signal: ' . $stat['stopsig']);
        }

        $code = proc_close($proc);
        $code = $stat['exitcode'];
        return $code;
    }

    function log($level, $msg)
    {
        if ($this->current_callback) {
            call_user_func($this->current_callback, 'output', $msg);
            return;
        }
        return Logger::log($level, $msg);
    }
}
