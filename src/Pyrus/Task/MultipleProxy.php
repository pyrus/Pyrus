<?php
/**
 * PEAR2_Pyrus_Task_MultipleProxy, container for multiple tasks to be executed
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
 * A container for multiple tasks
 *
 * This is used when a file contains multiple copies of the same task name,
 * such as multiple <tasks:replace> tags
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Task_MultipleProxy
{
    protected $tasks = array();
    protected $name;
    /**
     * @param array a group of tasks
     */
    function __construct(array $tasks, $name)
    {
        $this->tasks = $tasks;
        $this->name = $name;
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
        foreach ($this->tasks as $task) {
            $task->startSession($pkg, $fp, $dest);
            if (!rewind($fp)) {
                throw new PEAR2_Pyrus_Task_Exception('task ' . $this->name .
                                                          ' closed the file pointer, invalid task');
            }
        }
    }
}
?>