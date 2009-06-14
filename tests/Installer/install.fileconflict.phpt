--TEST--
PEAR2_Pyrus_Installer: install failure: file conflict between 2 downloaded packages
--FILE--
<?php

define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/upgrade.packagesplitting',
                       'http://pear2.php.net/');
PEAR2_Pyrus::$downloadClass = 'Internet';
PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('pear2/P1-1.0.0'));
PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('pear2/P2'));
try {
    PEAR2_Pyrus_Installer::commit();
    throw new Exception('passed and should have failed');
} catch (PEAR2_Pyrus_Installer_Exception $e) {
    $test->assertEquals('File conflicts detected:
 Package pear2.php.net/P1:
  php/glooby2 (conflicts with package pear2.php.net/P2)
', $e->getMessage(), 'file conflict');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===