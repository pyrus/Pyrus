<?php
/**
 * PEAR2_Pyrus_PackageFile_v2Iterator_FileContents
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
 * Traverse the current <dir> in the <contents> tag
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_PackageFile_v2Iterator_FileContents extends RecursiveArrayIterator
{
    protected $tag;
    protected $dir = '';
    private $_packagefile;
    function __construct($arr, $tag, PEAR2_Pyrus_PackageFile_v2 $parent, $dir = '')
    {
        $this->tag = $tag;
        $this->dir = $dir;
        $this->_packagefile = $parent;
        if ($arr instanceof PEAR2_Pyrus_PackageFile_v2Iterator_FileTag) {
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
            return new PEAR2_Pyrus_PackageFile_v2Iterator_FileContentsMulti($arr, $this->key(),
                $this->_packagefile, $dir . $now);
        }
        return new PEAR2_Pyrus_PackageFile_v2Iterator_FileContents($arr, $this->key(),
            $this->_packagefile, $dir . $now);
    }

    function hasChildren()
    {
        $arr = $this->current();
        if (!($arr instanceof PEAR2_Pyrus_PackageFile_v2Iterator_FileTag) && !is_array($arr)) {
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
        return new PEAR2_Pyrus_PackageFile_v2Iterator_FileTag($x, $this->dir, $this->_packagefile);
    }
}
