<?php
/**
 * \Pyrus\Logger
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

namespace Pyrus;

/**
 * Standard logging class for Pyrus
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class Logger
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

    static public function attach(LogInterface $observer)
    {
        self::$observers[get_class($observer)] = $observer;
    }

    static public function detach(LogInterface $observer)
    {
        unset(self::$observers[get_class($observer)]);
    }
}