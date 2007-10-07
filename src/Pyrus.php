<?php
class PEAR2_Pyrus
{
    static function getDataPath()
    {
        static $val = false;
        if ($val) return $val;
        $val = dirname(dirname(dirname(__FILE__))) . '/data/pear2.php.net/PEAR2_Pyrus';
        return $val;
    }
}
?>
