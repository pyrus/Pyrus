--TEST--
\pear2\Pyrus\Installer::prepare(), resolve the correct dependency version from 2 options, dep is in installed package
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

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/installer.prepare.dep.versionconflict.installed',
                       'http://pear2.php.net/');
\pear2\Pyrus\Main::$downloadClass = 'Internet';
class boo extends \pear2\Pyrus\Installer
{
    static $installPackages = array();
}
boo::begin();
boo::prepare(new \pear2\Pyrus\Package('pear2/P4-stable', true));
boo::preCommitDependencyResolve();
$test->assertEquals(1, count(boo::$installPackages), '1 package should be installed');
$pnames = array();
foreach (boo::$installPackages as $package) {
    $pnames[] = $package->name;
    switch ($package->name) {
        case 'P4' :
            $test->assertEquals('1.0.0', $package->version['release'], 'verify we got 1.0.0');
            break;
    }
}
sort($pnames);
$test->assertEquals(array('P4'), $pnames, 'correct packages');
boo::commit();

boo::begin();
boo::prepare(new \pear2\Pyrus\Package('pear2/P1', true));
boo::preCommitDependencyResolve();
$pnames = array();
foreach (boo::$installPackages as $package) {
    $pnames[] = $package->name;
    switch ($package->name) {
        case 'P1' :
        case 'P3' :
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
sort($pnames);
$test->assertEquals(array('P1', 'P2', 'P3'), $pnames, 'correct packages');
boo::commit();
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===