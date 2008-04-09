<?php
/**
 * PEAR2_Pyrus_FileTransactions_Rename
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
 * Handle files which are renamed on installation. This alters the standard file
 * transaction to rename an installed file to a different filename.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_FileTransactions_Rename implements PEAR2_Pyrus_IFileTransaction
{
    public function check($data, &$errors)
    {
        if (!file_exists($data[0])) {
            $errors[] = "cannot rename file $data[0], doesn't exist";
        }
        // check that dest dir. is writable
        if (!is_writable(dirname($data[1]))) {
            $errors[] = "permission denied ($type): $data[1]";
        }
    }

    public function commit($data, &$errors)
    {
        if (file_exists($data[1])) {
            $test = @unlink($data[1]);
        } else {
            $test = null;
        }
        if (!$test && file_exists($data[1])) {
            if ($data[2]) {
                $extra = ', this extension must be installed manually.  Rename to "' .
                    basename($data[1]) . '"';
            } else {
                $extra = '';
            }
            if (!isset($this->_options['soft'])) {
                PEAR2_Pyrus_Log::log(1, 'Could not delete ' . $data[1] . ', cannot rename ' .
                    $data[0] . $extra);
            }
            if (!isset($this->_options['ignore-errors'])) {
                return false;
            }
        }
        // permissions issues with rename - copy() is far superior
        $perms = @fileperms($data[0]);
        if (!@copy($data[0], $data[1])) {
            PEAR2_Pyrus_Log::log(1, 'Could not rename ' . $data[0] . ' to ' . $data[1] .
                ' ' . $php_errormsg);
            return false;
        }
        // copy over permissions, otherwise they are lost
        @chmod($data[1], $perms);
        @unlink($data[0]);
        PEAR2_Pyrus_Log::log(3, "+ mv $data[0] $data[1]");
    }

    public function rollback($data, &$errors)
    {
        @unlink($data[0]);
        PEAR2_Pyrus_Log::log(3, "+ rm $data[0]");
    }

    public function cleanup(){}
}