<?php
/**
 * \PEAR2\Pyrus\PackageFile\v2\Files\File
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
 * Class for manipulating a filelist's file contents
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\PackageFile\v2\Files;
class File extends \ArrayObject
{
    protected $pkg;
    protected $parent;
    protected $tasksNs;

    function __construct(\PEAR2\Pyrus\PackageFile\v2\Files $parent, \PEAR2\Pyrus\PackageFile\v2 $ultraparent, array $info)
    {
        $this->parent = $parent;
        $this->pkg = $ultraparent;
        $this->tasksNs = $ultraparent->getTasksNs() . ':';
        parent::__construct($info);
    }

    function __get($var)
    {
        $task = str_replace('-', ' ', $var);
        $task = str_replace(' ', '/', ucwords($task));
        $task = str_replace('/', '_', $task);
        $c = \PEAR2\Pyrus\Task\Common::getTask($var);
        if (isset($this[$this->tasksNs . $var])) {
            $inf = $this[$this->tasksNs . $var];
            if (isset($inf[0])) {
                $ret = array();
                foreach ($inf as $info) {
                    $ret[] = new $c($this->pkg, \PEAR2\Pyrus\Validate::NORMAL, $info, $this['attribs'], null);
                }

                goto return_multi;
            } else {
                $ret = array(new $c($this->pkg, \PEAR2\Pyrus\Validate::NORMAL, $inf, $this['attribs'], null));
                goto return_multi;
            }
        }

        $ret = array();
return_multi:
        return new \PEAR2\Pyrus\Task\MultipleProxy($this->pkg, $ret, $this['attribs'], $var);
    }

    function __set($var, $value)
    {
        if (!($value instanceof \PEAR2\Pyrus\Task\Common) && !($value instanceof \PEAR2\Pyrus\Task\MultipleProxy)) {
            throw new \PEAR2\Pyrus\PackageFile\Exception('Can only change tasks via __set() to an instance ' .
                                                        'of \PEAR2\Pyrus\Task\Common');
        }

        $this[$this->tasksNs . $var] = $value->getInfo();
        $this->pkg->setFilelistFile($this['attribs']['name'], $this->getArrayCopy());
    }
}