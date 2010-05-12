--TEST--
\PEAR2\Pyrus\Installer::prepare(), composite dep conflict 4
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
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';
\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare(new \PEAR2\Pyrus\Package('pear2/P3-1.0.0'));
\PEAR2\Pyrus\Installer::prepare(new \PEAR2\Pyrus\Package('pear2/P1-1.0.0'));
try {
    \PEAR2\Pyrus\Installer::preCommitDependencyResolve();
    throw new Exception('should have failed, did not');
} catch (\PEAR2\Pyrus\Installer\Exception $e) {
    $test->assertEquals('Dependency validation failed for some packages to install, installation aborted', $e->getMessage(),
                        'right error message');
    $test->assertIsa('\PEAR2\Pyrus\Package\Dependency\Set\Exception', $e->getPrevious(), 'cause class');
    $test->assertEquals('No versions of pear2.php.net/P1 or of its dependencies that can be installed because of:
pear2.php.net/P3 depends on: pear2.php.net/P2 (>= 1.0.0,<= 1.3.0,!= [1.2.3])
pear2.php.net/P1 depends on: pear2.php.net/P2 (>= 1.1.0,<= 2.0.0,!= [1.2.0,1.2.3],recommends 1.3.1)
', $e->getPrevious()->getMessage(), 'cause message');
}
\PEAR2\Pyrus\Installer::rollback();
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===