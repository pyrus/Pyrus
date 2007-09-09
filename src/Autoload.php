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
        if (!class_exists($class, false)) {
            throw new Exception('Class ' . $class . ' was not present in ' .
                str_replace('_', '/', $class) . '.php (include_path="' . get_include_path() .
                '") [PEAR2_Autoload version 1.0]');
        }
        return true;
    }
    throw new Exception('Class ' . $class . ' could not be loaded from ' .
        str_replace('_', '/', $class) . '.php (include_path="' . get_include_path() .
        '") [PEAR2_Autoload version 1.0]');
}

// set up __autoload
if (function_exists('spl_autoload_register')) {
    if (!($_____t = spl_autoload_functions()) || !in_array('PEAR2_Autoload', spl_autoload_functions())) {
        spl_autoload_register('PEAR2_Autoload');
        if (function_exists('__autoload') && ($_____t === false)) {
            // __autoload() was being used, but now would be ignored, add
            // it to the autoload stack
            spl_autoload_register('__autoload');
        }
    }
    unset($_____t);
} else {
    function __autoload($class) { return PEAR2_Autoload($class); }
}

// set up include_path if it doesn't register our current location
$____paths = explode(PATH_SEPARATOR, get_include_path());
$____found = false;
foreach ($____paths as $____path) {
    if ($____path == dirname(dirname(__FILE__))) {
        $____found = true;
        break;
    }
}
if (!$____found) {
    set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)));
}
unset($____paths);
unset($____path);
unset($____found);
