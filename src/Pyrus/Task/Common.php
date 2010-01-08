<?php
/**
 * PEAR_Task_Common, base class for installer tasks
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
 * A task is an operation that manipulates the contents of a file.
 *
 * Simple tasks operate on 1 file.  Multiple tasks are executed after all files have been
 * processed and installed, and are designed to operate on all files containing the task.
 * The Post-install script task simply takes advantage of the fact that it will be run
 * after installation, replace is a simple task.
 *
 * Combining tasks is possible, but ordering is significant.
 *
 * <file name="test.php" role="php">
 *  <tasks:replace from="@data-dir@" to="data_dir" type="pear-config"/>
 *  <tasks:postinstallscript/>
 * </file>
 *
 * This will first replace any instance of @data-dir@ in the test.php file
 * with the path to the current data directory.  Then, it will include the
 * test.php file and run the script it contains to configure the package post-installation.
 *
 * The observer pattern is used, so that updates from a MultipleProxy from one
 * member task can be used to save changes to the parent file object.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus\Task;
abstract class Common extends \ArrayObject implements \SplSubject
{
    static protected $customtasks = array();
    const PACKAGE = 1;
    const INSTALL = 2;
    const PACKAGEANDINSTALL = 3;
    const POSTINSTALL = 4; // this is used by post-install scripts
    /**
     * Valid types for this version are 'simple' and 'multiple'
     *
     * - simple tasks operate on the contents of a file and write out changes to disk
     * - multiple tasks operate on the contents of many files and write out the
     *   changes directly to disk
     *
     * Child task classes must override this property.
     * @access protected
     */
    const TYPE = 'simple';
    /**
     * Determines which install phase this task is executed under
     */
    const PHASE = Common::INSTALL;
    /**
     * @param PEAR_Config
     * @param PEAR_Common
     */

    static $multiple = array();

    protected $installphase;
    protected $xml;
    protected $taskAttributes;
    protected $lastVersion;
    protected $pkg;
    protected $observers = array();

    /**
     * Initialize a task instance with the parameters
     * @param \pear2\Pyrus\Package|\pear2\Pyrus\PackageFileInterface package information
     * @param int install phase
     * @param array raw, parsed xml
     * @param array attributes from the <file> tag containing this task
     * @param string|null last installed version of this package
     */
    function __construct($pkg, $phase, $xml, $attribs, $lastversion)
    {
        $this->pkg = $pkg;
        $this->installphase = $phase;
        $this->xml = $xml;
        $this->taskAttributes = $attribs;
        $this->lastVersion = $lastversion;
        if (static::TYPE == 'multiple') {
            self::$multiple[get_class($this)][] = $this;
        }
    }

    /**
     * Validate the basic contents of a task tag.
     *
     * On error, one of the \pear2\Pyrus\Task\Exception\* exceptions should be thrown.
     *
     *  - {@link \pear2\Pyrus\Task\Exception\NoAttributes}: use this exception for
     *    missing attributes that should be present.
     *  - {@link \pear2\Pyrus\Task\Exception\MissingAttribute}: use this exception
     *    for a specific missing attribute.
     *  - {@link \pear2\Pyrus\Task\Exception\WrongAttributeValue}: use this
     *    exception for an incorrect value for an attribute.
     *  - {@link \pear2\Pyrus\Task\Exception\InvalidTask}: use this exception for
     *    general validation errors
     *
     * It is also possible to throw multiple validation errors, by using a
     * {@link \pear2\MultiErrors} object as a cause parameter to
     * {@link \pear2\Pyrus\Task\Exception}.
     * @param PEAR_Pyrus_PackageFileInterface
     * @param array
     * @param array the entire parsed <file> tag
     * @param string the filename of the package.xml
     * @throws \pear2\Pyrus\Task\Exception
     * @throws \pear2\Pyrus\Task\Exception\NoAttributes
     * @throws \pear2\Pyrus\Task\Exception\MissingAttribute
     * @throws \pear2\Pyrus\Task\Exception\WrongAttributeValue
     * @throws \pear2\Pyrus\Task\Exception\InvalidTask
     * @abstract
     */
    static function validateXml(\pear2\Pyrus\PackageInterface $pkg, $xml, $fileXml, $file)
    {
    }

    /**
     * Begin a task processing session.  All multiple tasks will be processed after each file
     * has been successfully installed, all simple tasks should perform their task here and
     * return any errors using the custom throwError() method to allow forward compatibility
     *
     * This method MUST NOT write out any changes to disk
     * @param PEAR_Pyrus_PackageFileInterface
     * @param resource open file pointer, set to the beginning of the file
     * @param string the eventual final file location (informational only)
     * @return string|false false to skip this file, otherwise return the new contents
     * @throws \pear2\Pyrus\Task\Exception on errors, throw this exception
     * @abstract
     */
    function startSession($fp, $dest)
    {
    }

    function setupPostInstall()
    {
    }

    /**
     * This method is used to process each of the tasks for a particular multiple class
     * type.  Simple tasks need not implement this method.
     * @param array an array of tasks
     * @access protected
     * @static
     * @abstract
     */
    function run($tasks)
    {
    }

    final static function hasPostinstallTasks()
    {
        return count(self::$multiple);
    }

    final static function runPostinstallTasks()
    {
        foreach (self::$multiple as $class => $tasks) {
            $class::run(self::$multiple[$class]);
        }
        self::$multiple[$class] = array();
    }

    function isPreProcessed()
    {
        return false;
    }

    function getInfo()
    {
        return $this->xml;
    }

    function attach(\SplObserver $observer)
    {
        $this->observers[] = $observer;
    }

    function detach(\SplObserver $observed)
    {
        foreach ($this->observers as $i => $observer) {
            if ($observer === $observed) {
                unset($this->observers[$i]);
                $this->observers = array_values($this->observers);
                return;
            }
        }
    }

    function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    /**
     * Determine whether a task name is a valid task.  Custom tasks may be defined
     * using subdirectories by putting a "-" in the name, as in <tasks:mycustom-task>
     *
     * Note that this method will auto-load the task class file and test for the existence
     * of the name with "-" replaced by "_" as in PEAR/Task/mycustom/task.php makes class
     * PEAR_Task_mycustom_task
     * @param string
     * @return boolean
     */
    static function getTask($task)
    {
        if (!count(static::$customtasks)) {
            static::registerBuiltinTasks();
        }
        if ($pos = strpos($task, ':')) {
            $task = substr($task, $pos + 1);
        }
        if (isset(static::$customtasks[$task])) {
            $test = static::$customtasks[$task]['class'];
            if (class_exists($test, true)) {
                return $test;
            }
        }
        return false;
    }

    static function registerBuiltinTasks()
    {
        static::registerCustomTask(array('name' => 'replace',
                                         'class' => 'pear2\Pyrus\Task\Replace'));
        static::registerCustomTask(array('name' => 'windowseol',
                                         'class' => 'pear2\Pyrus\Task\Windowseol'));
        static::registerCustomTask(array('name' => 'unixeol',
                                         'class' => 'pear2\Pyrus\Task\Unixeol'));
        static::registerCustomTask(array('name' => 'postinstallscript',
                                         'class' => 'pear2\Pyrus\Task\Postinstallscript'));
    }

    static function registerCustomTask($taskinfo)
    {
        static::$customtasks[$taskinfo['name']] = $taskinfo;
    }
}