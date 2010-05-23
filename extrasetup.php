<?php
/**
 * This file generates the pyrus.phar file and PEAR2 package for Pyrus.
 */
$lib_path = __DIR__ . '/..';
if (basename(__DIR__) == 'trunk') {
    $lib_path .= '/..';
}

$extrafiles = array(
    new \PEAR2\Pyrus\Package($lib_path . '/HTTP_Request/trunk/package.xml'),
    new \PEAR2\Pyrus\Package($lib_path . '/sandbox/Console_CommandLine/trunk/package.xml'),
    new \PEAR2\Pyrus\Package($lib_path . '/MultiErrors/trunk/package.xml'),
    new \PEAR2\Pyrus\Package($lib_path . '/Exception/trunk/package.xml'),
);
