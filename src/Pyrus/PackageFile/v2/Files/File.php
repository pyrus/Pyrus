<?php
/**
 * PEAR2_Pyrus_PackageFile_v2_Files_File
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
 * Class for manipulating a filelist's file contents
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_PackageFile_v2_Files_File extends ArrayObject
{
    protected $pkg;
    protected $parent;
    protected $tasksNs;

    function __construct(PEAR2_Pyrus_PackageFile_v2_Files $parent, PEAR2_Pyrus_PackageFile_v2 $ultraparent, array $info)
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
        $c = 'PEAR2_Pyrus_Task_' . $task;
        if (isset($this[$this->tasksNs . $var])) {
            $inf = $this[$this->tasksNs . $var];
            if (isset($inf[0])) {
                $ret = array();
                foreach ($inf as $info) {
                    $ret[] = new $c($this->pkg, PEAR2_Pyrus_Validate::NORMAL, $info, $this['attribs'], null);
                }
                goto return_multi;
            } else {
                $c = 'PEAR2_Pyrus_Task_' . str_replace('-', '_', $var);
                $ret = array(new $c($this->pkg, PEAR2_Pyrus_Validate::NORMAL, $inf, $this['attribs'], null));
                goto return_multi;
            }
        }
        $ret = array();
return_multi:
        return new PEAR2_Pyrus_Task_MultipleProxy($this->pkg, $ret, $this['attribs'], $var);
    }

    function __set($var, $value)
    {
        if (!($value instanceof PEAR2_Pyrus_Task_Common) && !($value instanceof PEAR2_Pyrus_Task_MultipleProxy)) {
            throw new PEAR2_Pyrus_PackageFile_Exception('Can only change tasks via __set() to an instance ' .
                                                        'of PEAR2_Pyrus_Task_Common');
        }
        $this[$this->tasksNs . $var] = $value->getInfo();
        $this->pkg->setFilelistFile($this['attribs']['name'], $this->getArrayCopy());
    }
}