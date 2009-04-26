<?php
/**
 * PEAR2_Pyrus_AtomicFileTransaction_Collection
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
 * Container for all transaction objects, can commit/rollback in one fell swoop
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_AtomicFileTransaction_Collection
{
    protected $transactions = array();
    protected $intransaction = false;

    function __destruct()
    {
        if (count($this->transactions)) {
            $this->rollback();
        }
    }

    function getTransaction(PEAR2_Pyrus_IPackage $package, PEAR2_Pyrus_Installer_Role_Common $role)
    {
        $info = PEAR2_Pyrus_Installer_Role_Common::getInfo(get_class($role));
        $rolename = strtolower(str_replace('PEAR2_Pyrus_Installer_Role_', '', get_class($role)));
        if ($info['honorsbaseinstall']) {
            // for roles like php that put all package's files into the same directory
            $key = $rolename;
        } else {
            $key = $package->channel . '/' . $package->name . '-' . $rolename;
        }
        if (!isset($this->transactions[$key])) {
            $rolepath = PEAR2_Pyrus_Config::current()->{$info['location_config']};
            if (!$info['honorsbaseinstall']) {
                $rolepath .=
                    DIRECTORY_SEPARATOR . $package->channel . DIRECTORY_SEPARATOR . $package->name;
            }
            $this->transactions[$key] = new PEAR2_Pyrus_AtomicFileTransaction($role,
                $rolepath);
            if ($this->intransaction) {
                $this->transactions[$key]->begin();
            }
        }
        return $this->transactions[$key];
    }

    function begin()
    {
        if ($this->intransaction) {
            throw new PEAR2_Pyrus_AtomicFileTransaction_Exception(
                'Cannot begin a new transaction, in an active transaction');
        }
        $this->intransaction = true;
    }

    function rollback()
    {
        if (!$this->intransaction) {
            return;
        }
        foreach ($this->transactions as $transaction) {
            $transaction->rollback();
        }
    }

    function commit()
    {
        foreach ($this->transactions as $transaction) {
            $transaction->commit();
        }
        $this->intransaction = false;
        $this->transactions = array();
    }
}