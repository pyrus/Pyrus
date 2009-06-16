<?php
/**
 * PEAR2_Pyrus_Log
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
 * Standard logging class for Pyrus
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Log
{
    static public $log = array();
    static public $maxlevel = 7;
    static protected $observers = array();
    static public function log($level, $message)
    {
        if (count(self::$observers)) {
            foreach (self::$observers as $observer) {
                $observer->log($level, $message);
            }
            return;
        }
        for ($i = $level; $i <= self::$maxlevel; $i++) {
            self::$log[$i][] = $message;
        }
    }

    static public function attach(PEAR2_Pyrus_ILog $observer)
    {
        self::$observers[spl_object_hash($observer)] = $observer;
    }

    static public function detach(PEAR2_Pyrus_ILog $observer)
    {
        unset(self::$observers[spl_object_hash($observer)]);
    }
}