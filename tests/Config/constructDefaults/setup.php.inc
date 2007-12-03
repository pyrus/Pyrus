<?php
set_include_path('C:/development/pear-core/testpear/php;C:/php5/pear');
require dirname(dirname(__FILE__)) . '/setup.php.inc';
class tc extends PEAR2_Pyrus_Config
{
    public static function getTestDefaults($nophpini = false)
    {
        if ($nophpini) {
            $a = self::$defaults;
            unset($a['php_ini']);
            return $a;
        }
        return self::$defaults;
    }

    public static function constructDefaults()
    {
        return parent::constructDefaults();
    }
}
