<?php
/**
 * \pear2\Pyrus\PackageFile\v2Iterator\ScriptFileFilterIterator
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
 * Class which iterates over all files, only returning those that contain script tasks.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus\PackageFile\v2Iterator;
class ScriptFileFilterIterator extends \FilterIterator
{
    private $_inner;
    private $_parent;
    private $_tasksNs;
    function __construct(array $arr, \pear2\Pyrus\PackageFileInterface $parent)
    {
        $this->_parent = $parent;
        $this->_tasksNs = $this->_parent->getTasksNs();
        parent::__construct($this->_inner = new \ArrayIterator($arr));
    }

    function accept()
    {
        if (!$this->_inner->valid()) {
            return false;
        }

        $file = $this->key();
        foreach ($this->_inner->current() as $key => $value) {
            if (strpos($key, $this->_tasksNs . ':') !== 0) {
                continue;
            }

            $class = \pear2\Pyrus\Task\Common::getTask($key);
            if (!class_exists($class, true)) {
                continue;
            }

            if ($class::TYPE == 'script') {
                return true;
            }
        }

        return false;
    }

    function current()
    {
        return $this->_parent->files[$this->_inner->key()];
    }
}