#!/usr/bin/env php -d detect_unicode=0
<?php
if (version_compare(phpversion(), '5.3.1', '<')) {
    if (substr(phpversion(), 0, 5) != '5.3.1') {
        // this small hack is because of running RCs of 5.3.1
        echo "Pyrus requires PHP 5.3.1 or newer.\n";
        exit(1);
    }
}

$missing_extensions = array();
foreach (array('phar', 'spl', 'pcre', 'simplexml', 'libxml', 'xmlreader', 'sqlite3')
         as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}
if ($missing_extensions) {
    echo "You must compile PHP with the following extensions enabled:\n",
        implode(', ', $missing_extensions), "\n",
        "or install the necessary extensions for your distribution.\n";
    exit(1);
}
unset($missing_extensions);

// Reject old libxml installations
// moved to version 2.6.20 so XMLReader::setSchema can be used.
if (version_compare(LIBXML_DOTTED_VERSION, '2.6.20', '<')) {
    echo "Pyrus requires libxml >= 2.6.20."
         . " Version detected: " . LIBXML_DOTTED_VERSION . "\n";
    exit(1);
}

// Make sure phar:// includes are available
if (extension_loaded('suhosin')) {
    if (strpos(ini_get('suhosin.executor.include.whitelist'), 'phar') === false
        || strpos(ini_get('suhosin.executor.include.blacklist'), 'phar') !== false
    ) {
        echo "Pyrus requires phar:// includes to be enabled.\n\n"
             . "Add suhosin.executor.include.whitelist=\"phar\" to your php.ini.\n";
        exit(1);
    }
}

try {
    Phar::mapPhar();
} catch (Exception $e) {
    echo "Cannot process Pyrus phar:\n";
    echo $e->getMessage(), "\n";
    exit(1);
}

function pyrus_autoload($class)
{
    $class = str_replace(array('_','\\'), '/', $class);
    if (file_exists('phar://' . __FILE__ . '/Pyrus-@PACKAGE_VERSION@/php/' . $class . '.php')) {
        include 'phar://' . __FILE__ . '/Pyrus-@PACKAGE_VERSION@/php/' . $class . '.php';
    }
}

spl_autoload_register("pyrus_autoload");
$phar = new Phar(__FILE__);
$sig = $phar->getSignature();
define('PYRUS_SIG', $sig['hash']);
define('PYRUS_SIGTYPE', $sig['hash_type']);

$frontend = new \Pyrus\ScriptFrontend\Commands;
@array_shift($_SERVER['argv']);
$frontend->run($_SERVER['argv']);
__HALT_COMPILER();
