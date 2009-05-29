<?php
/**
 * PEAR2_Pyrus_PECLBuild
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
 * Class handling building of PECL packages
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_PECLBuild
{
    protected $ui;

    function __construct($ui)
    {
        $this->ui = $ui;
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
            $date = new DateTime;
            $date->setTimestamp($info['mtime']);
    
            echo $info['ino'], ' ', str_pad(($info['blocks']/2), 3, ' ', STR_PAD_LEFT), ' ', $perminfo, ' ',
                 $info['nlink'], ' ', $uname, ' ', $gname, ' ',
                 str_pad($info['size'], 10, ' ', STR_PAD_LEFT), ' ', $date->format('Y-m-d H:i'), ' ', $file, "\n";
        }
    }

    /**
     * @param string
     * @param string
     * @param array
     * @access private
     */
    function _harvestInstDir($dest_prefix, $dirname)
    {
        $built_files = array();
        foreach (new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dirname,
                                                    0|\RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            $built_files[] = array(
                        'file' => (string) $file,
                        'dest' => $dest_prefix . str_replace($dirname . DIRECTORY_SEPARATOR, '', $file),
                        'php_api' => $this->php_api_version,
                        'zend_mod_api' => $this->zend_module_api_no,
                        'zend_ext_api' => $this->zend_extension_api_no,
                        );
        }
        return $built_files;
    }

    /**
     * Build an extension from source.  Runs "phpize" in the source
     * directory, and compiles there.
     *
     * @param PEAR2_Pyrus_IPackage $descfile package object
     *
     * @param mixed $callback callback function used to report output,
     * see PEAR_Builder::_runCommand for details
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
     * @see PEAR_Builder::_runCommand
     */
    function build($descfile, $callback = null)
    {
        $config = $config;
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
        $dir = $config->src_dir . DIRECTORY_SEPARATOR .
            $pkg->channel . DIRECTORY_SEPARATOR . $pkg->name;
        $old_cwd = getcwd();
        if (!file_exists($dir) || !is_dir($dir) || !chdir($dir)) {
            throw new PEAR2_Pyrus_PECLBuild_Exception('could not chdir to package directory ' . $dir);
        }
        if (!is_writable($dir)) {
            throw new PEAR2_Pyrus_PECLBuild_Exception('cannot build in package directory ' . $dir .
                                                      ', directory not writable');
        }
        $this->log(0, "cleaning build directory $dir");
        if (!$this->_runCommand($config->php_prefix
                                . "phpize" .
                                $config->php_suffix . ' --clean',
                                null,
                                array('PATH' => $config->php_bin . ':' . getenv('PATH')))) {
            throw new PEAR2_Pyrus_PECLBuild_Exception('phpize failed - if running phpize manually from ' . $dir .
                                                      ' works, please open a bug for pyrus with details');
        }
        $this->log(0, "building in $dir");
        if (!$this->_runCommand($config->php_prefix
                                . "phpize" .
                                $config->php_suffix,
                                array($this, 'phpizeCallback'),
                                array('PATH' => $config->php_bin . ':' . getenv('PATH')))) {
            throw new PEAR2_Pyrus_PECLBuild_Exception('phpize failed - if running phpize manually from ' . $dir .
                                                      ' works, please open a bug for pyrus with details');
        }

        // {{{ start of interactive part
        $configure_command = "$dir/configure";
        if (count($pkg->configureoptions)) {
            foreach ($pkg->configureoptions as $o) {
                list($r) = $this->ui->ask($o->prompt, array(), $o->default);
                if (substr($o->name, 0, 5) == 'with-' &&
                    ($r == 'yes' || $r == 'autodetect')) {
                    $configure_command .= ' --' . $o->name;
                } else {
                    $configure_command .= ' --' . $o->name . '=' . trim($r);
                }
            }
        }
        // }}} end of interactive part

        $build_basedir = $dir;
        $build_dir = $dir;
        $inst_dir = $build_basedir . '/.install';
        $this->log(1, "building in $build_dir");
        if (!file_exists($inst_dir) || !is_dir($inst_dir) || !mkdir($inst_dir, 0755, true)) {
            throw new PEAR2_Pyrus_PECLBuild_Exception('could not create temporary install dir: ' . $inst_dir);
        }

        if (getenv('MAKE')) {
            $make_command = getenv('MAKE');
        } else {
            $make_command = 'make';
        }
        $to_run = array(
            $configure_command,
            $make_command,
            "$make_command INSTALL_ROOT=\"$inst_dir\" install",
            );
        if (!file_exists($build_dir) || !is_dir($build_dir) || !chdir($build_dir)) {
            throw new PEAR2_Pyrus_PECLBuild_Exception('could not chdir to ' . $build_dir);
        }
        $env = $_ENV;
        // this next line is modified by the installer at packaging time
        if ('@PEAR-VER@' == '@'.'PEAR-VER@') {
            // we're running from svn
            $env['PHP_PEAR_VERSION'] = '2.0.0a1';
        } else {
            $env['PHP_PEAR_VERSION'] = '@PEAR-VER@';
        }
        foreach ($to_run as $cmd) {
            try {
                if (!$this->_runCommand($cmd, $callback, $env)) {
                    throw new PEAR2_Pyrus_PECLBuild_Exception("`$cmd' failed");
                }
            } catch (\Exception $e) {
                chdir($old_cwd);
                throw $e;
            }
        }

        $this->listInstalledStuff($inst_dir);

        if (!file_exists('modules') || !is_dir('modules')) {
            chdir($old_cwd);
            throw new PEAR2_Pyrus_PECLBuild_Exception("no `modules' directory found");
        }
        $built_files = array();
        $prefix = exec($config->php_prefix
                        . "php-config" .
                       $config->php_suffix . " --prefix");
        $this->_harvestInstDir($prefix, $inst_dir . DIRECTORY_SEPARATOR . $prefix, $built_files);
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
        if (preg_match('/^\s+(\S[^:]+):\s+(\d{8})/', $data, $matches)) {
            $member = preg_replace('/[^a-z]/', '_', strtolower($matches[1]));
            $apino = (int)$matches[2];
            if (isset($this->$member)) {
                $this->$member = $apino;
            }
        }
    }

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
     *
     * @access private
     */
    function _runCommand($command, $callback = null, $env = null)
    {
        $this->log(1, "running: $command");

        $exitcode = $this->system_with_timeout($command, $this->buildDirectory, $callback, $env);
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
                proc_terminate($proc);
                throw new PEAR2_Pyrus_PECLBuild_Exception('Error: Process timed out');
            } else if ($n > 0) {
                if (strlen($line) == 0) {
                        /* EOF */
                        break;
                }
                while ($line = fgets($pipes[1], 1024)) {
                    if ($callback) {
                        call_user_func($callback, 'cmdoutput', $line);
                    } else {
                        $this->log(0, rtrim($line));
                    }
                }
            }
        }

        $stat = proc_get_status($proc);

        if ($stat['signaled']) {
            $code = proc_close($proc);
            throw new PEAR2_Pyrus_PECLBuild_Exception('Process was stopped with a signal: ' . $stat['stopsig']);
        }

        $code = proc_close($proc);
        return $code;
    }

    function log($level, $msg)
    {
        if ($this->current_callback) {
            call_user_func($this->current_callback, 'output', $msg);
            return;
        }
        return PEAR2_Pyrus_Log::log($level, $msg);
    }
}