--TEST--
\pear2\Pyrus\Installer::prepare(), composite dep conflict 3
--FILE--
<?php
/**
 * Test a dependency tree like so:
 *
 * P1 -> P2 recommended 1.2.0
 * P3 -> P2 recommended 1.1.0
 *
 * to test composite dep failure
 */

define('MYDIR', __DIR__);
include __DIR__ . '/../setup.php.inc';
require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/installer.prepare.depconflict2',
                       'http://pear2.php.net/');
\pear2\Pyrus\Main::$downloadClass = 'Internet';
\pear2\Pyrus\Installer::begin();
\pear2\Pyrus\Installer::prepare(new \pear2\Pyrus\Package('pear2/P3-1.0.0'));
\pear2\Pyrus\Installer::prepare(new \pear2\Pyrus\Package('pear2/P1-1.0.0'));
try {
    \pear2\Pyrus\Installer::preCommitDependencyResolve();
    throw new Exception('should have failed, did not');
} catch (\pear2\Pyrus\Package\Exception $e) {
    $test->assertEquals('Cannot install pear2.php.net/P2, two dependencies conflict (' .
                        'different recommended values for pear2.php.net/P1 and pear2.php.net/P3)', $e->getMessage(),
                        'right cause message');
}
\pear2\Pyrus\Installer::rollback();
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===