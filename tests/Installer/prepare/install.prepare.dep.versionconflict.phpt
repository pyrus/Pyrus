--TEST--
PEAR2_Pyrus_Installer::prepare(), resolve the correct dependency version from 2 options
--FILE--
<?php
/**
 * Test a dependency tree like so:
 *
 * P1 -> P2 >= 1.2.0 (1.2.3 is latest version)
 *
 * P2 1.2.3 -> P3
 *          -> P5
 *
 * P2 1.2.2 -> P3
 *
 * P3
 *
 * P4 -> P2 != 1.2.3
 *
 * P5
 *
 * This causes a conflict when P1 and P4 are installed that must resolve to installing:
 *
 * P1
 * P2 1.2.2
 * P3
 * P4
 */

define('MYDIR', __DIR__);
include __DIR__ . '/../setup.php.inc';
require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/installer.prepare.dep.versionconflict',
                       'http://pear2.php.net/');
PEAR2_Pyrus_REST::$downloadClass = 'Internet';
class boo extends PEAR2_Pyrus_Installer
{
    static $installPackages = array();
}
boo::begin();
boo::prepare(new PEAR2_Pyrus_Package('pear2/P1-1.0.0'));
boo::prepare(new PEAR2_Pyrus_Package('pear2/P4-stable'));
boo::preCommitDependencyResolve();
$test->assertEquals(4, count(boo::$installPackages), '4 packages should be installed');
foreach (boo::$installPackages as $package) {
    switch ($package->name) {
        case 'P1' :
        case 'P3' :
        case 'P4' :
            // these should be in there and we don't care about version
            break;
        case 'P5' :
            $test->assertFalse(true, 'Package P5 downloaded');
            break;
        case 'P2' :
            $test->assertEquals('1.2.2', $package->version['release'], 'verify we got 1.2.2');
            break;
        default:
            $test->assertEquals(false, $package->name, 'wrong package downloaded');
    }
}
boo::rollback();
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===