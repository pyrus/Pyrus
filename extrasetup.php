<?php
/**
 * This file generates the pyrus.phar file and PEAR2 package for Pyrus.
 */
$extrafiles = array(
    new \pear2\Pyrus\Package(__DIR__ . '/../HTTP_Request/package.xml'),
    new \pear2\Pyrus\Package(__DIR__ . '/../sandbox/Console_CommandLine/package.xml'),
    new \pear2\Pyrus\Package(__DIR__ . '/../MultiErrors/package.xml'),
    new \pear2\Pyrus\Package(__DIR__ . '/../Exception/package.xml'),
);
