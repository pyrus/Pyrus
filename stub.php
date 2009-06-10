<?php
function pyrus_autoload($class)
{
    if (file_exists('phar://' . __FILE__ . '/php/' . implode('/', explode('_', $class)) . '.php')) {
        include 'phar://' . __FILE__ . '/php/' . implode('/', explode('_', $class)) . '.php';
    }
}
spl_autoload_register("pyrus_autoload");
$frontend = new PEAR2_Pyrus_ScriptFrontend_Commands;
@array_shift($_SERVER['argv']);
$frontend->run($_SERVER['argv']);
__HALT_COMPILER();