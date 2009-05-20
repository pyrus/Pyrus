--TEST--
PEAR2_Pyrus_Installer::prepare(), composite dep conflict 1
--FILE--
<?php
/**
 * Test a dependency tree like so:
 *
 * P1 -> P2 >= 1.2.0
 * P3 -> P2 <= 1.1.0
 *
 * to test composite dep failure
 */

define('MYDIR', __DIR__);
include __DIR__ . '/../setup.php.inc';
require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/installer.prepare.depconflict1',
                       'http://pear2.php.net/');
PEAR2_Pyrus_REST::$downloadClass = 'Internet';
PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('pear2/P1-1.0.0'));
PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('pear2/P3-1.0.0', true));
try {
    PEAR2_Pyrus_Installer::preCommitDependencyResolve();
    throw new Exception('should have failed, did not');
} catch (PEAR2_Pyrus_Package_Exception $e) {
    $test->assertEquals('Cannot install pear2.php.net/P2, two dependencies conflict (' .
                        'pear2.php.net/P3 max is > pear2.php.net/P1 min)', $e->getMessage(),
                        'right cause message');
}
PEAR2_Pyrus_Installer::rollback();
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===