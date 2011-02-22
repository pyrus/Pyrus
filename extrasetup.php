<?php
/**
 * This file generates the pyrus.phar file and PEAR2 package for Pyrus.
 */

$current = \PEAR2\Pyrus\Config::current();
$config = \PEAR2\Pyrus\Config::singleton(__DIR__ . '/vendor');

$extrafiles = array(
    $config->registry->toPackage('PEAR2_HTTP_Request', 'pear2.php.net'),
    $config->registry->toPackage('PEAR2_Console_CommandLine', 'pear2.php.net'),
    $config->registry->toPackage('PEAR2_Exception', 'pear2.php.net'),
    $config->registry->toPackage('PEAR2_MultiErrors', 'pear2.php.net')
);



\PEAR2\Pyrus\Config::setCurrent($current->path);

