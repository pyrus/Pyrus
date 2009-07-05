<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
class tc extends \pear2\Pyrus\Config
{
    public static function getTestDefaults($nophpini = false)
    {
        return self::$defaults;
    }

    public static function constructDefaults()
    {
        return parent::constructDefaults();
    }
}
