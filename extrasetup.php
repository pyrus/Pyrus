<?php
/**
 * This file generates the pyrus.phar file and PEAR2 package for Pyrus.
 */
$lib_path = __DIR__ . '/..';
$xml_path = 'package.xml';

if (basename(__DIR__) == 'trunk') {
    $lib_path .= '/..';
    $xml_path = 'trunk/package.xml';
}

$extrafiles = array(
    new \PEAR2\Pyrus\Package($lib_path . '/HTTP_Request/'.$xml_path),
    new \PEAR2\Pyrus\Package($lib_path . '/sandbox/Console_CommandLine/'.$xml_path),
    new \PEAR2\Pyrus\Package($lib_path . '/MultiErrors/'.$xml_path),
    new \PEAR2\Pyrus\Package($lib_path . '/Exception/'.$xml_path),
);
