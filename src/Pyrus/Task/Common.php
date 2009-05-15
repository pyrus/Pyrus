<?php
/**
 * PEAR_Task_Common, base class for installer tasks
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
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
abstract class PEAR2_Pyrus_Task_Common extends \ArrayObject
{
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
    const PHASE = PEAR2_Pyrus_Task_Common::INSTALL;
    /**
     * @param PEAR_Config
     * @param PEAR_Common
     */

    static $multiple = array();

    protected $installphase;
    protected $xml;
    protected $taskAttributes;
    protected $lastVersion;

    /**
     * Initialize a task instance with the parameters
     * @param array raw, parsed xml
     * @param array attributes from the <file> tag containing this task
     * @param string|null last installed version of this package
     */
    function __construct($phase, $xml, $attribs, $lastversion)
    {
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
     * On error, one of the PEAR2_Pyrus_Task_Exception_* exceptions should be thrown.
     *
     *  - {@link PEAR2_Pyrus_Task_Exception_NoAttributes}: use this exception for
     *    missing attributes that should be present.
     *  - {@link PEAR2_Pyrus_Task_Exception_MissingAttribute}: use this exception
     *    for a specific missing attribute.
     *  - {@link PEAR2_Pyrus_Task_Exception_WrongAttributeValue}: use this
     *    exception for an incorrect value for an attribute.
     *  - {@link PEAR2_Pyrus_Task_Exception_InvalidTask}: use this exception for
     *    general validation errors
     *
     * It is also possible to throw multiple validation errors, by using a
     * {@link PEAR2_MultiErrors} object as a cause parameter to
     * {@link PEAR2_Pyrus_Task_Exception}.
     * @param PEAR_Pyrus_IPackageFile
     * @param array
     * @param array the entire parsed <file> tag
     * @param string the filename of the package.xml
     * @throws PEAR2_Pyrus_Task_Exception
     * @throws PEAR2_Pyrus_Task_Exception_NoAttributes
     * @throws PEAR2_Pyrus_Task_Exception_MissingAttribute
     * @throws PEAR2_Pyrus_Task_Exception_WrongAttributeValue
     * @throws PEAR2_Pyrus_Task_Exception_InvalidTask
     * @abstract
     */
    static function validateXml(PEAR2_Pyrus_IPackage $pkg, $xml, $fileXml, $file)
    {
    }

    /**
     * Begin a task processing session.  All multiple tasks will be processed after each file
     * has been successfully installed, all simple tasks should perform their task here and
     * return any errors using the custom throwError() method to allow forward compatibility
     *
     * This method MUST NOT write out any changes to disk
     * @param PEAR_Pyrus_IPackageFile
     * @param resource open file pointer, set to the beginning of the file
     * @param string the eventual final file location (informational only)
     * @return string|false false to skip this file, otherwise return the new contents
     * @throws PEAR2_Pyrus_Task_Exception on errors, throw this exception
     * @abstract
     */
    function startSession(PEAR2_Pyrus_IPackage $pkg, $fp, $dest)
    {
    }


    function runPostInstall(PEAR2_Pyrus_IPackage $pkg, $path)
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
}
?>