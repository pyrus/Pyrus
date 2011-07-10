<?php
/**
 * This file generates the pyrus.phar file and PEAR2 package for Pyrus.
 */

$current = \Pyrus\Config::current();
$config = \Pyrus\Config::singleton(__DIR__ . '/vendor');

$extrafiles = array(
    $config->registry->toPackage('PEAR2_HTTP_Request', 'pear2.php.net'),
    $config->registry->toPackage('PEAR2_Console_CommandLine', 'pear2.php.net'),
    $config->registry->toPackage('PEAR2_Exception', 'pear2.php.net'),
    $config->registry->toPackage('PEAR2_MultiErrors', 'pear2.php.net')
);



\Pyrus\Config::setCurrent($current->path);

