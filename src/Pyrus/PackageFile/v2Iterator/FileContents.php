<?php
/**
 * \Pyrus\PackageFile\v2Iterator\FileContents
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
 * Traverse the current <dir> in the <contents> tag
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\PackageFile\v2Iterator;
class FileContents extends \RecursiveArrayIterator
{
    protected $tag;
    protected $dir = '';
    private $_packagefile;

    function __construct($arr, $tag, \Pyrus\PackageFile\v2 $parent, $dir = '')
    {
        $this->tag = $tag;
        $this->dir = $dir;
        $this->_packagefile = $parent;
        if ($arr instanceof FileTag) {
            $arr = $arr->getArrayCopy();
        }

        parent::__construct($arr);
    }

    function getChildren ()
    {
        $arr = $this->current();
        $now = '';
        if ($this->key() == 'dir' && !isset($arr[0])) {
            $now = $arr['attribs']['name'];
            if (!$this->dir && $now == '/') {
                $now = '';
            }
        }

        $dir = $this->dir;
        if ($now && $dir) {
            if ($dir[strlen($dir) - 1] != '/') {
                $dir .= '/';
            }
        }

        if (isset($arr['attribs'])) unset($arr['attribs']);
        if (isset($arr[0])) {
            return new FileContentsMulti($arr, $this->key(), $this->_packagefile, $dir . $now);
        }

        return new FileContents($arr, $this->key(), $this->_packagefile, $dir . $now);
    }

    function hasChildren()
    {
        $arr = $this->current();
        if (!($arr instanceof FileTag) && !is_array($arr)) {
            return false;
        }

        if (isset($arr['file']) || isset($arr['dir']) || isset($arr[0])) {
            return true;
        }

        return false;
    }

    function current()
    {
        $x = parent::current();
        return new FileTag($x, $this->dir, $this->_packagefile);
    }
}