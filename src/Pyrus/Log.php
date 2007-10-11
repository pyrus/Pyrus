<?php
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