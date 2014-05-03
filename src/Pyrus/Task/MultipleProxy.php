<?php
/**
 * \Pyrus\Task\MultipleProxy, container for multiple tasks to be executed
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
namespace Pyrus\Task;

/**
 * A container for multiple tasks
 *
 * This is used when a file contains multiple copies of the same task name,
 * such as multiple <tasks:replace> tags
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class MultipleProxy extends \ArrayObject implements \IteratorAggregate, \SplObserver
{
    protected $name;
    protected $parent;
    protected $fileattribs;
    /**
     * @param \Pyrus\PackageInterface|\Pyrus\PackageFileInterface
     * @param array a group of tasks
     */
    function __construct($parent, array $tasks, $fileattribs, $name)
    {
        $this->parent = $parent;
        $this->name = $name;
        $this->fileattribs = $fileattribs;
        parent::__construct($tasks);
        foreach ($tasks as $task) {
            $task->attach($this);
        }
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
     * @throws \Pyrus\Task\Exception on errors, throw this exception
     * @abstract
     */
    function startSession($fp, $dest)
    {
        foreach ($this as $task) {
            $task->startSession($fp, $dest);
            if (!rewind($fp)) {
                throw new Exception('task ' . $this->name . ' closed the file pointer, invalid task');
            }
        }
    }

    function isPreProcessed()
    {
        foreach ($this as $task) {
            if (!$task->isPreProcessed()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Iterate over each task and aggregate their content, then pass it to the parent
     * package object
     */
    function getInfo()
    {
        $ret = array();
        foreach ($this as $task) {
            $ret[] = $task->getInfo();
        }

        if (count($ret) == 1) {
            $ret = $ret[0];
        }

        return $ret;
    }

    function add()
    {
        $c = Common::getTask($this->name);
        $ret = new $c($this->parent, \Pyrus\Validate::NORMAL, array(), $this->fileattribs, null);
        $this[] = $ret;
        $ret->attach($this);
        return $ret;
    }

    function update(\SplSubject $subject)
    {
        $ret = $subject->getInfo();
        if (empty($ret)) {
            foreach ($this as $i => $task) {
                if ($subject === $task) {
                    unset($this[$i]);
                    $this->exchangeArray(array_values($this->getArrayCopy()));
                    break;
                }
            }
        }

        $this->parent->files[$this->fileattribs['name']]->{$this->name} = $this;
    }
}