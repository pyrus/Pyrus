<?php
/**
 * <tasks:postinstallscript>
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
 * Implements the postinstallscript file task.
 *
 * Note that post-install scripts are handled separately from installation, by the
 * "pyrus run-scripts" command
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Task_Postinstallscript extends PEAR2_Pyrus_Task_Common
{
    const TYPE = 'script';
    const PHASE = PEAR2_Pyrus_Task_Common::POSTINSTALL;
    protected $scriptClass;
    protected $obj;

    /**
     * Initialize a task instance with the parameters
     * @param array raw, parsed xml
     * @param array attributes from the <file> tag containing this task
     * @param string|null last installed version of this package, if any (useful for upgrades)
     */
    function __construct($phase, $xml, $attribs, $lastversion)
    {
        parent::__construct($phase, $xml, $attribs, $lastversion);
        $this->scriptClass = str_replace(array('/', '.php'), array('_', ''), $fileattribs['name']);
        $this->_filename = $fileattribs['name'];
    }

    /**
     * Validate the basic contents of a <postinstallscript> tag
     * @param PEAR_Pyrus_IPackageFile
     * @param array
     * @param array the entire parsed <file> tag
     * @param string the filename of the package.xml
     * @throws PEAR2_Pyrus_Task_Exception_InvalidTask
     */
    static function validateXml(PEAR2_Pyrus_IPackage $pkg, $xml, $fileXml, $file)
    {
        if ($fileXml['role'] != 'php') {
            throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                             'Post-install script "' .
                                                             $fileXml['name'] . '" must be role="php"');
        }
        try {
            $file = $pkg->getFileContents($fileXml['name']);
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                             'Post-install script "' .
                                                             $fileXml['name'] . '" is not valid: ' .
                                                             $e->getMessage(), $e);
        }
        if ($file === null) {
            throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                             'Post-install script "' .
                                                             $fileXml['name'] . '" could not be ' .
                                                             'retrieved for processing!');
        } else {
            $validator = $pkg->getValidator();
            $analysis = $validator->analyzeSourceCode($file, true);
            if (!$analysis) {
                $warnings = '';
                // iterate over the problems
                foreach ($validator->getErrors() as $warn) {
                    $warnings .= $warn->getMessage() . "\n";
                }
                throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file, 
                                                                 'Analysis of post-install script "' .
                                                                 $fileXml['name'] . '" failed: ' . $warnings,
                                                                 $validator->getErrors());
            }
            if (count($analysis['declared_classes']) != 1) {
                throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                 'Post-install script "' .
                                                                 $fileXml['name'] .
                                                                 '" must declare exactly 1 class');
            }
            $class = $analysis['declared_classes'][0];
            if ($class != str_replace(array('/', '.php'), array('_', ''),
                  $fileXml['name']) . '_postinstall') {
                throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                 'Post-install script "' .
                                                                 $fileXml['name'] . '" class "' .
                                                                 $class . '" must be named "' .
                                                                 str_replace(array('/', '.php'),
                                                                             array('_', ''),
                                                                             $fileXml['name']) .
                                                                 '_postinstall"');
            }
            if (!isset($analysis['declared_methods'][$class])) {
                throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                 'Post-install script "' .
                                                                 $fileXml['name'] .
                                                                 '" must declare methods init() and run()');
            }
            $methods = array('init' => 0, 'run' => 1);
            foreach ($analysis['declared_methods'][$class] as $method) {
                if (isset($methods[$method])) {
                    unset($methods[$method]);
                }
            }
            if (count($methods)) {
                throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                 'Post-install script "' .
                                                                 $fileXml['name'] .
                                                                 '" must declare methods init() and run()');
            }
        }
        $definedparams = array();
        $tasksNamespace = $pkg->getTasksNs() . ':';
        if (!isset($xml[$tasksNamespace . 'paramgroup']) && isset($xml['paramgroup'])) {
            // in order to support the older betas, which did not expect internal tags
            // to also use the namespace
            $tasksNamespace = '';
        }
        if (isset($xml[$tasksNamespace . 'paramgroup'])) {
            $params = $xml[$tasksNamespace . 'paramgroup'];
            if (!is_array($params) || !isset($params[0])) {
                $params = array($params);
            }
            foreach ($params as $param) {
                if (!isset($param[$tasksNamespace . 'id'])) {
                    throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                 'Post-install script "' .
                                                                 $fileXml['name'] .
                                                                 '" <paramgroup> must have ' .
                                                                 'an ' . $tasksNamespace . 'id> tag');
                }
                if (isset($param[$tasksNamespace . 'name'])) {
                    if (!in_array($param[$tasksNamespace . 'name'], $definedparams)) {
                    throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                 'Post-install script "' .
                                                                 $fileXml['name'] . '" ' . $tasksNamespace .
                                                                 'paramgroup> id "' .
                                                                 $param[$tasksNamespace .'id'] .
                                                                 '" parameter "' .
                                                                 $param[$tasksNamespace . 'name'] .
                                                                 '" has not been previously defined');
                    }
                    if (!isset($param[$tasksNamespace . 'conditiontype'])) {
                        throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                         'Post-install script "' .
                                                                         $fileXml['name'] . '" ' .
                                                                         $tasksNamespace .
                                                                         'paramgroup> id "' .
                                                                         $param[$tasksNamespace . 'id'] .
                                                                         '" must have a ' . $tasksNamespace .
                                                                         'conditiontype> tag ' .
                                                                         'containing either "=", ' .
                                                                         '"!=", or "preg_match"');
                    }
                    if (!in_array($param[$tasksNamespace . 'conditiontype'],
                          array('=', '!=', 'preg_match'))) {
                        throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                         'Post-install script "' .
                                                                         $fileXml['name'] . '" ' .
                                                                         $tasksNamespace .
                                                                         'paramgroup> id "' .
                                                                         $param[$tasksNamespace . 'id'] .
                                                                         '" must have a ' . $tasksNamespace .
                                                                         'conditiontype> tag ' .
                                                                         'containing either "=", ' .
                                                                         '"!=", or "preg_match"');
                    }
                    if (!isset($param[$tasksNamespace . 'value'])) {
                        throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                         'Post-install script "' .
                                                                         $fileXml['name'] . '" ' .
                                                                         $tasksNamespace .
                                                                         'paramgroup> id "' .
                                                                         $param[$tasksNamespace . 'id'] .
                                                                         '" must have a ' .
                                                                         $tasksNamespace .
                                                                         'value> tag containing ' .
                                                                         'expected parameter value');
                    }
                }
                if (isset($param[$tasksNamespace . 'instructions'])) {
                    if (!is_string($param[$tasksNamespace . 'instructions'])) {
                        throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                         'Post-install script "' .
                                                                         $fileXml['name'] . '" ' .
                                                                         $tasksNamespace .
                                                                         'paramgroup> id "' .
                                                                         $param[$tasksNamespace . 'id'] .
                                                                         '" ' . $tasksNamespace .
                                                                         'instructions> must be simple text');
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
                        throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                         'Post-install script "' .
                                                                         $fileXml['name'] . '" parameter for ' .
                                                                         $tasksNamespace . 'paramgroup> id "' .
                                                                         $param[$tasksNamespace . 'id'] .
                                                                         '" must have ' .
                                                                         'a ' . $tasksNamespace . 'name> tag');
                    }
                    if (!preg_match('/[a-zA-Z0-9]+/',
                          $subparam[$tasksNamespace . 'name'])) {
                        throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                         'Post-install script "' .
                                                                         $fileXml['name'] . '" parameter "' .
                                                                         $subparam[$tasksNamespace . 'name'] .
                                                                         '" for ' . $tasksNamespace .
                                                                         'paramgroup> id "' .
                                                                         $param[$tasksNamespace . 'id'] .
                                                                         '" is not a valid name.  Must ' .
                                                                         'contain only alphanumeric characters');
                    }
                    if (!isset($subparam[$tasksNamespace . 'prompt'])) {
                        throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                         'Post-install script "' .
                                                                         $fileXml['name'] . '" parameter "' .
                                                                         $subparam[$tasksNamespace . 'name'] .
                                                                         '" for ' . $tasksNamespace .
                                                                         'paramgroup> id "' .
                                                                         $param[$tasksNamespace . 'id'] .
                                                                         '" must have a ' . $tasksNamespace .
                                                                         'prompt> tag');
                    }
                    if (!isset($subparam[$tasksNamespace . 'type'])) {
                        throw new PEAR2_Pyrus_Task_Exception_InvalidTask('postinstallscript', $file,
                                                                         'Post-install script "' .
                                                                         $fileXml['name'] . '" parameter "' .
                                                                         $subparam[$tasksNamespace . 'name'] .
                                                                         '" for ' . $tasksNamespace .
                                                                         'paramgroup> id "' .
                                                                         $param[$tasksNamespace . 'id'] .
                                                                         '" must have a ' . $tasksNamespace .
                                                                         'type> tag');
                    }
                    $definedparams[] = $param[$tasksNamespace . 'id'] . '::' .
                    $subparam[$tasksNamespace . 'name'];
                }
            }
        }
        return true;
    }

    /**
     * Unlike other tasks, the installed file name is passed in instead of the file contents,
     * because this task is handled post-installation
     * @param PEAR2_Pyrus_IPackage
     * @param string path to the post-install script
     * @return bool false to skip this file
     */
    function runPostInstall(PEAR2_Pyrus_IPackage $pkg, $path)
    {
        if ($this->installphase != PEAR2_Pyrus_Task_Common::INSTALL) {
            return false;
        }
        PEAR2_Pyrus_Log::log(0, 'Including external post-installation script "' .
            $path . '" - any errors are in this script');
        include $path;
        if (class_exists($this->scriptClass)) {
            PEAR2_Pyrus_Log::log(0, 'Inclusion succeeded');
        } else {
            throw new PEAR2_Pyrus_Task_Exception('init of post-install script class "' . $this->scriptClass
                . '" failed');
        }
        $this->obj = new $this->scriptClass;
        PEAR2_Pyrus_Log::log(1, 'running post-install script "' . $this->scriptClass . '->init()"');
        try {
            $this->obj->init($this->config, $pkg, $this->lastVersion);
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Task_Exception('init of post-install script "' . $this->scriptClass .
                '->init()" failed', $e);
        }
        PEAR2_Pyrus_Log::log(0, 'init succeeded');
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
    function run()
    {
    }

    function __get($var)
    {
        if ($var === 'params') {
            return $this->xml;
        }
        if ($var === 'scriptobject') {
            return $this->obj;
        }
        if ($var === 'paramgroup') {
            if (!isset($this->xml) || !is_array($this->xml)) {
                $params = array();
            } else {
                $params = $this->xml;
                if (!isset($params[0])) {
                    $params = array($params);
                }
            }
            return new PEAR2_Pyrus_Task_Postinstallscript_Paramgroup($this, $params);
        }
        throw new PEAR2_Pyrus_Task_Exception('Invalid variable ' . $var . 'requested from Post-install script task');
    }

    function setParamgroups($info)
    {
        if ($info === null) {
            unset($this->xml['paramgroup']);
            return;
        }
        $this->xml['paramgroup'] = $info;
    }
}
?>