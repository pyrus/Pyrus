<?php
function PEAR2_Autoload($class)
{
    if (substr($class, 0, 6) !== 'PEAR2_') {
        return false;
    }
    $fp = @fopen(str_replace('_', '/', $class) . '.php', 'r', true);
    if ($fp) {
        fclose($fp);
        require str_replace('_', '/', $class) . '.php';
        return true;
    }
    throw new Exception('Class $class could not be loaded from ' .
        str_replace('_', '/', $class) . '.php (include_path="' . get_include_path() .
        '")');
}
if (!function_exists('__autoload')) {
    function __autoload($class) { return PEAR2_Autoload($class); }
}