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
 * Handle directory removal for file transactions.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_FileTransactions_Rmdir implements PEAR2_Pyrus_IFileTransaction
{
    public function check($data, &$errors)
    {
        
    }

    public function commit($data, &$errors)
    {
        if (file_exists($data[0])) {
            do {
                $testme = opendir($data[0]);
                while (false !== ($entry = readdir($testme))) {
                    if ($entry == '.' || $entry == '..') {
                        continue;
                    }
                    closedir($testme);
                    break 2; // this directory is not empty and can't be
                             // deleted
                }
                closedir($testme);
                if (!@rmdir($data[0])) {
                    PEAR2_Pyrus_Log::log(1, 'Could not rmdir ' . $data[0] . ' ' .
                        $php_errormsg);
                    return false;
                }
                $this->log(3, "+ rmdir $data[0]");
            } while (false);
        }
    }

    public function rollback($data, &$errors)
    {
        
    }

    public function cleanup(){}
}