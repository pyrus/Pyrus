<?php
if (version_compare(phpversion(), '5.3.0', '<')) {
    if (substr(phpversion(), 0, 5) != '5.3.0') {
        // this small hack is because of running RCs of 5.3.0
        echo "Pyrus requires PHP 5.3.0 or newer.\n";
        exit -1;
    }
}
foreach (array('phar', 'spl', 'pcre', 'simplexml') as $ext) {
    if (!extension_loaded($ext)) {
        echo 'Extension ', $ext, " is required\n";
        exit -1;
    }
}
try {
    Phar::mapPhar();
} catch (Exception $e) {
    echo "Cannot process Pyrus phar:\n";
    echo $e->getMessage(), "\n";
    exit -1;
}
function pyrus_autoload($class)
{
    $class = str_replace('_', '\\', $class);
    if (file_exists('phar://' . __FILE__ . '/php/' . implode('/', explode('\\', $class)) . '.php')) {
        include 'phar://' . __FILE__ . '/php/' . implode('/', explode('\\', $class)) . '.php';
    }
}
spl_autoload_register("pyrus_autoload");
$frontend = new \pear2\Pyrus\ScriptFrontend\Commands;
@array_shift($_SERVER['argv']);
$frontend->run($_SERVER['argv']);
__HALT_COMPILER();