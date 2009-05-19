--TEST--
PEAR2_Pyrus_Installer::prepare(), duplicate package
--FILE--
<?php
define('MYDIR', __DIR__);
include __DIR__ . '/../setup.php.inc';
class boo extends PEAR2_Pyrus_Installer
{
    static $preinstallPackages = array();
}
boo::begin();
boo::prepare($package);
$test->assertEquals(1, count(boo::$preinstallPackages), 'first prepare');
boo::prepare($package);
$test->assertEquals(1, count(boo::$preinstallPackages), 'second prepare');
boo::rollback();
$test->assertEquals(0, count(boo::$preinstallPackages), 'rollback');
// this passes if no exceptions are thrown
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===