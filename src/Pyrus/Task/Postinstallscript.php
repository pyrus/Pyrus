<?php
/**
 * <tasks:postinstallscript>
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
 * Implements the postinstallscript file task.
 *
 * Note that post-install scripts are handled separately from installation, by the
 * "pyrus run-scripts" command
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus\Task;
use \pear2\Pyrus\Logger as Logger;
class Postinstallscript extends Common
{
    const TYPE = 'script';
    const PHASE = Common::POSTINSTALL;
    protected $scriptClass;
    protected $obj;

    /**
     * Initialize a task instance with the parameters
     * @param array raw, parsed xml
     * @param array attributes from the <file> tag containing this task
     * @param string|null last installed version of this package, if any (useful for upgrades)
     */
    function __construct($pkg, $phase, $xml, $attribs, $lastversion)
    {
        parent::__construct($pkg, $phase, $xml, $attribs, $lastversion);
        $this->scriptClass = str_replace(array('/', '.php'), array('_', ''), $attribs['name']) . '_postinstall';
        $this->_filename = $attribs['name'];
    }

    /**
     * Validate the basic contents of a <postinstallscript> tag
     * @param PEAR_Pyrus_PackageFileInterface
     * @param array
     * @param array the entire parsed <file> tag
     * @param string the filename of the package.xml
     * @throws \pear2\Pyrus\Task\Exception\InvalidTask
     */
    static function validateXml(\pear2\Pyrus\PackageInterface $pkg, $xml, $fileXml, $file)
    {
        if ($fileXml['role'] != 'php') {
            throw new Exception\InvalidTask('postinstallscript', $file,
                                                             'Post-install script "' .
                                                             $fileXml['name'] . '" must be role="php"');
        }
        try {
            $filecontents = $pkg->getFileContents($fileXml['name']);
        } catch (\Exception $e) {
            throw new Exception\InvalidTask('postinstallscript', $file,
                                                             'Post-install script "' .
                                                             $fileXml['name'] . '" is not valid: ' .
                                                             $e->getMessage(), $e);
        }

        $validator = $pkg->getValidator();
        $analysis = $validator->analyzeSourceCode($filecontents, true);
        if (!$analysis) {
            $warnings = array();
            // iterate over the problems
            foreach ($validator->getErrors() as $warn) {
                $warnings[] = $warn->getMessage();
            }
            $warnings = implode($warnings);
            throw new Exception\InvalidTask('postinstallscript', $file,
                                                             'Analysis of post-install script "' .
                                                             $fileXml['name'] . '" failed: ' . $warnings,
                                                             $validator->getErrors());
        }
        if (count($analysis['declared_classes']) != 1) {
            throw new Exception\InvalidTask('postinstallscript', $file,
                                                             'Post-install script "' .
                                                             $fileXml['name'] .
                                                             '" must declare exactly 1 class');
        }
        $class = $analysis['declared_classes'][0];
        if ($class != str_replace(array('/', '.php'), array('_', ''),
              $fileXml['name']) . '_postinstall') {
            throw new Exception\InvalidTask('postinstallscript', $file,
                                                             'Post-install script "' .
                                                             $fileXml['name'] . '" class "' .
                                                             $class . '" must be named "' .
                                                             str_replace(array('/', '.php'),
                                                                         array('_', ''),
                                                                         $fileXml['name']) .
                                                             '_postinstall"');
        }
        if (!isset($analysis['declared_methods'][$class])) {
            throw new Exception\InvalidTask('postinstallscript', $file,
                                                             'Post-install script "' .
                                                             $fileXml['name'] .
                                                             '" must declare methods init2() and run2()');
        }
        $methods = array('init2' => 0, 'run2' => 1);
        foreach ($analysis['declared_methods'][$class] as $method) {
            if (isset($methods[$method])) {
                unset($methods[$method]);
            }
        }
        if (count($methods)) {
            throw new Exception\InvalidTask('postinstallscript', $file,
                                                             'Post-install script "' .
                                                             $fileXml['name'] .
                                                             '" must declare methods init2() and run2()');
        }
        $definedparams = array();
        $tasksNamespace = $pkg->getTasksNs() . ':';
        if (!isset($xml[$tasksNamespace . 'paramgroup'])) {
            return true;
        }
        $params = $xml[$tasksNamespace . 'paramgroup'];
        if (!is_array($params) || !isset($params[0])) {
            $params = array($params);
        }
        foreach ($params as $param) {
            if (!isset($param[$tasksNamespace . 'id'])) {
                throw new Exception\InvalidTask('postinstallscript', $file,
                                                             'Post-install script "' .
                                                             $fileXml['name'] .
                                                             '" <paramgroup> must have ' .
                                                             'an <id> tag');
            }
            if (isset($param[$tasksNamespace . 'name'])) {
                if (!in_array($param[$tasksNamespace . 'name'], $definedparams)) {
                    throw new Exception\InvalidTask('postinstallscript', $file,
                                                    'Post-install script "' .
                                                    $fileXml['name'] . '" ' .
                                                    '<paramgroup> id "' .
                                                    $param[$tasksNamespace .'id'] .
                                                    '" conditiontype parameter "' .
                                                    $param[$tasksNamespace . 'name'] .
                                                    '" has not been previously defined');
                }
                if (!isset($param[$tasksNamespace . 'conditiontype'])) {
                    throw new Exception\InvalidTask('postinstallscript', $file,
                                                    'Post-install script "' .
                                                    $fileXml['name'] . '" ' .
                                                    '<paramgroup> id "' .
                                                    $param[$tasksNamespace . 'id'] .
                                                    '" must have a ' .
                                                    '<conditiontype> tag ' .
                                                    'containing either "=", ' .
                                                    '"!=", or "preg_match"');
                }
                if (!in_array($param[$tasksNamespace . 'conditiontype'],
                      array('=', '!=', 'preg_match'))) {
                    throw new Exception\InvalidTask('postinstallscript', $file,
                                                    'Post-install script "' .
                                                    $fileXml['name'] . '" ' .
                                                    '<paramgroup> id "' .
                                                    $param[$tasksNamespace . 'id'] .
                                                    '" must have a ' .
                                                    '<conditiontype> tag ' .
                                                    'containing either "=", ' .
                                                    '"!=", or "preg_match"');
                }
                if (!isset($param[$tasksNamespace . 'value'])) {
                    throw new Exception\InvalidTask('postinstallscript', $file,
                                                    'Post-install script "' .
                                                    $fileXml['name'] . '" ' .
                                                    '<paramgroup> id "' .
                                                    $param[$tasksNamespace . 'id'] .
                                                    '" must have a ' .
                                                    '<value> tag containing ' .
                                                    'expected parameter value');
                }
            }
            if (isset($param[$tasksNamespace . 'instructions'])) {
                if (!is_string($param[$tasksNamespace . 'instructions'])) {
                    throw new Exception\InvalidTask('postinstallscript', $file,
                                                    'Post-install script "' .
                                                    $fileXml['name'] . '" ' .
                                                    '<paramgroup> id "' .
                                                    $param[$tasksNamespace . 'id'] .
                                                    '" <instructions> must be simple text');
                }
            }
            if (!isset($param[$tasksNamespace . 'param'])) {
                continue; // <param> is no longer required
            }
            $subparams = $param[$tasksNamespace . 'param'];
            if (!is_array($subparams) || !isset($subparams[0])) {
                $subparams = array($subparams);
            }
            foreach ($subparams as $subparam) {
                if (!isset($subparam[$tasksNamespace . 'name'])) {
                    throw new Exception\InvalidTask('postinstallscript', $file,
                                                    'Post-install script "' .
                                                    $fileXml['name'] . '" parameter for ' .
                                                    '<paramgroup> id "' .
                                                    $param[$tasksNamespace . 'id'] .
                                                    '" must have ' .
                                                    'a <name> tag');
                }
                if (!preg_match('/[a-zA-Z0-9]+/',
                      $subparam[$tasksNamespace . 'name'])) {
                    throw new Exception\InvalidTask('postinstallscript', $file,
                                                    'Post-install script "' .
                                                    $fileXml['name'] . '" parameter "' .
                                                    $subparam[$tasksNamespace . 'name'] .
                                                    '" for ' .
                                                    '<paramgroup> id "' .
                                                    $param[$tasksNamespace . 'id'] .
                                                    '" is not a valid name.  Must ' .
                                                    'contain only alphanumeric characters');
                }
                if (!isset($subparam[$tasksNamespace . 'prompt'])) {
                    throw new Exception\InvalidTask('postinstallscript', $file,
                                                    'Post-install script "' .
                                                    $fileXml['name'] . '" parameter "' .
                                                    $subparam[$tasksNamespace . 'name'] .
                                                    '" for ' .
                                                    '<paramgroup> id "' .
                                                    $param[$tasksNamespace . 'id'] .
                                                    '" must have a ' .
                                                    '<prompt> tag');
                }
                if (!isset($subparam[$tasksNamespace . 'type'])) {
                    throw new Exception\InvalidTask('postinstallscript', $file,
                                                    'Post-install script "' .
                                                    $fileXml['name'] . '" parameter "' .
                                                    $subparam[$tasksNamespace . 'name'] .
                                                    '" for ' .
                                                    '<paramgroup> id "' .
                                                    $param[$tasksNamespace . 'id'] .
                                                    '" must have a ' .
                                                    '<type> tag');
                }
                $definedparams[] = $param[$tasksNamespace . 'id'] . '::' .
                $subparam[$tasksNamespace . 'name'];
            }
        }
        return true;
    }

    /**
     * Unlike other tasks, the installed file name is passed in instead of the file contents,
     * because this task is handled post-installation
     * @param \pear2\Pyrus\PackageInterface
     * @param string path to the post-install script
     * @return bool false to skip this file
     */
    function setupPostInstall()
    {
        $files = \pear2\Pyrus\Config::current()->registry->info($this->pkg->name, $this->pkg->channel,
                                                               'installedfiles');
        foreach ($files as $path => $info) {
            if ($info['name'] == $this->_filename) {
                break;
            }
        }
        Logger::log(0, 'Including external post-installation script "' . $path .
                       '" - any errors are in this script');
        include $path;
        if (class_exists($this->scriptClass) === false) {
            throw new Exception('init of post-install script class "' . $this->scriptClass . '" failed');
        }

        Logger::log(0, 'Inclusion succeeded');
        $this->obj = new $this->scriptClass;
        Logger::log(1, 'running post-install script "' . $this->scriptClass . '->init()"');
        try {
            $this->obj->init2($this->pkg, $this->lastVersion);
        } catch (\Exception $e) {
            throw new Exception('init of post-install script "' . $this->scriptClass .
                '->init()" failed', $e);
        }

        Logger::log(0, 'init succeeded');
        return true;
    }

    /**
     * No longer used
     * @see PEAR_PackageFile_v2::runPostinstallScripts()
     * @param array an array of tasks
     * @param string install or upgrade
     * @access protected
     * @static
     */
    function run($tasks)
    {
    }

    function __get($var)
    {
        if ($var === 'scriptobject') {
            return $this->obj;
        }

        if ($var === 'paramgroup') {
            if (!isset($this->xml) || !is_array($this->xml) || !isset($this->xml['paramgroup'])) {
                $params = array();
            } else {
                $params = $this->xml['paramgroup'];
                if (count($params) && !isset($params[0])) {
                    $params = array($params);
                }
            }

            return new Postinstallscript\Paramgroup($this->pkg->getTasksNs(), $this, $params);
        }

        throw new Exception('Invalid variable ' . $var . ' requested from Post-install script task');
    }

    function setParamgroups($info)
    {
        if ($info === null) {
            unset($this->xml['paramgroup']);
            return;
        }

        $this->xml['paramgroup'] = $info;
        $this->notify();
    }
}