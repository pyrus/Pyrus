<?php
/**
 * \Pyrus\PackageFile\v2\Files\File
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
 * Class for manipulating a filelist's file contents
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\PackageFile\v2\Files;
class File extends \ArrayObject
{
    protected $pkg;
    protected $parent;
    protected $tasksNs;

    function __construct(\Pyrus\PackageFile\v2\Files $parent, \Pyrus\PackageFile\v2 $ultraparent, array $info)
    {
        $this->parent = $parent;
        $this->pkg = $ultraparent;
        $this->tasksNs = $ultraparent->getTasksNs() . ':';
        parent::__construct($info);
    }

    function __get($var)
    {
        $ret = array();

        $task = str_replace('-', ' ', $var);
        $task = str_replace(' ', '/', ucwords($task));
        $task = str_replace('/', '_', $task);
        $c = \Pyrus\Task\Common::getTask($var);
        if (isset($this[$this->tasksNs . $var])) {
            $inf = $this[$this->tasksNs . $var];
            if (isset($inf[0])) {
                $ret = array();
                foreach ($inf as $info) {
                    $ret[] = new $c($this->pkg, \Pyrus\Validate::NORMAL, $info, $this['attribs'], null);
                }
            } else {
                $ret = array(new $c($this->pkg, \Pyrus\Validate::NORMAL, $inf, $this['attribs'], null));
            }
        }

        return new \Pyrus\Task\MultipleProxy($this->pkg, $ret, $this['attribs'], $var);
    }

    function __set($var, $value)
    {
        if (!($value instanceof \Pyrus\Task\Common) && !($value instanceof \Pyrus\Task\MultipleProxy)) {
            throw new \Pyrus\PackageFile\Exception('Can only change tasks via __set() to an instance ' .
                                                        'of \Pyrus\Task\Common');
        }

        $this[$this->tasksNs . $var] = $value->getInfo();
        $this->pkg->setFilelistFile($this['attribs']['name'], $this->getArrayCopy());
    }
}