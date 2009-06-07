--TEST--
PEAR2_Pyrus_Installer::prepare(), composite dep conflict 4
--FILE--
<?php
/**
 * In order to test excluding all possible versions
 * 
 * Create a dependency tree like so:
 *
 * P1 -> P2 min 1.1.0, exclude 1.2.0
 * P3 -> P2 max 1.3.0, exclude 1.2.3
 *
 * P2 only has releases for 1.0.0, 1.2.0, 1.2.3, and 1.3.1
 *
 * to test composite dep failure
 */

define('MYDIR', __DIR__);
include __DIR__ . '/../setup.php.inc';
require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/installer.prepare.depconflict4',
                       'http://pear2.php.net/');
PEAR2_Pyrus::$downloadClass = 'Internet';
PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('pear2/P3-1.0.0'));
PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('pear2/P1-1.0.0'));
try {
    PEAR2_Pyrus_Installer::preCommitDependencyResolve();
    throw new Exception('should have failed, did not');
} catch (PEAR2_Pyrus_Installer_Exception $e) {
    $test->assertEquals('Dependency validation failed for some packages to install, installation aborted', $e->getMessage(),
                        'right error message');
    $test->assertIsa('PEAR2_Pyrus_Channel_Exception', $e->getCause(), 'cause class');
    $test->assertEquals('Unable to locate a package release for pear2.php.net/P2 that can satisfy all dependencies', $e->getCause()->getMessage(), 'cause message');
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