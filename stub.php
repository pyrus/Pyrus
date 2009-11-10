#!/usr/bin/env php
<?php
if (version_compare(phpversion(), '5.3.1', '<')) {
    if (substr(phpversion(), 0, 5) != '5.3.1') {
        // this small hack is because of running RCs of 5.3.1
        echo "Pyrus requires PHP 5.3.1 or newer.\n";
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
    $class = str_replace(array('_','\\'), '/', $class);
    if (file_exists('phar://' . __FILE__ . '/PEAR2_Pyrus-@PACKAGE_VERSION@/php/' . $class . '.php')) {
        include 'phar://' . __FILE__ . '/PEAR2_Pyrus-@PACKAGE_VERSION@/php/' . $class . '.php';
    }
}
spl_autoload_register("pyrus_autoload");
$phar = new Phar(__FILE__);
$sig = $phar->getSignature();
define('PYRUS_SIG', $sig['hash']);
define('PYRUS_SIGTYPE', $sig['hash_type']);
$frontend = new \pear2\Pyrus\ScriptFrontend\Commands;
@array_shift($_SERVER['argv']);
$frontend->run($_SERVER['argv']);
__HALT_COMPILER();
