<?php
/**
 * File PHPDOC Comment
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
 * Handle files which are installed as a different file. This alters the file
 * transaction to place the file in a different location than normal.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_FileTransactions_Installedas implements PEAR2_Pyrus_IFileTransaction
{
    private $_dirTree = array();
    private $pkginfo;

    public function reset(PEAR2_Pyrus_Package $package)
    {
        $this->pkginfo = $package;
        $this->_dirTree = array();
    }

    public function check($data, &$errors)
    {
        
    }

    public function commit($data, &$errors)
    {
        //$this->pkginfo->setInstalledAs($data[0], $data[1]);
        if (!isset($this->_dirtree[dirname($data[1])])) {
            $this->_dirtree[dirname($data[1])] = true;
            $this->pkginfo->setDirtree(dirname($data[1]));

            while(!empty($data[3]) && $data[3] != '/' && $data[3] != '\\'
                  && $data[3] != '.') {
                $this->pkginfo->setDirtree($pp =
                    $this->_prependPath($data[3], $data[2]));
                $this->_dirtree[$pp] = true;
                $data[3] = dirname($data[3]);
            }
        }
    }
    function _prependPath($path, $prepend)
    {
        if (strlen($prepend) > 0) {
            if (strpos(PHP_OS, 'WIN') !== false && preg_match('/^[a-z]:/i', $path)) {
                if (preg_match('/^[a-z]:/i', $prepend)) {
                    $prepend = substr($prepend, 2);
                } elseif ($prepend{0} != '\\') {
                    $prepend = "\\$prepend";
                }
                $path = substr($path, 0, 2) . $prepend . substr($path, 2);
            } else {
                $path = $prepend . $path;
            }
        }
        return $path;
    }

    public function rollback($data, &$errors)
    {
        //$this->pkginfo->setInstalledAs($data[0], false);
    }

    public function cleanup()
    {
        //$this->pkginfo->resetDirTree();
    }
}