<?php
require_once 'PHPUnit/Autoload.php';
require_once 'PHP/CodeCoverage.php';

// Setup autoloader
function pyrus_autoload($class)
{
    if (strpos($class, 'PEAR2\\Pyrus\\') === false) {
        return false;
    }
    $class = str_replace(array('PEAR2\\', '_', '\\'), '/', $class);

    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $class . '.php';
    if (file_exists($path)) {
        include $path;
    }
}
spl_autoload_register("pyrus_autoload");

require_once __DIR__ . '/../vendor/php/PEAR2/Autoload.php';

define('PEAR_CONFIG_DEFAULT_PHP_BIN', 'c:\xampp\php\php.exe');