--TEST--
\PEAR2\Pyrus\Installer::prepare(), 2 versions of the same package potentially installed
--FILE--
<?php
/**
 * Create a dependency tree like so:
 *
 * P1-1.0.0
 * P1-1.1.0RC1
 *
 * P2 -> P3
 *
 * P3 -> P1
 */
use PEAR2\Pyrus\Package;
include __DIR__ . '/../setup.php.inc';
require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/install.prepare.duplicate',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';
class b extends \PEAR2\Pyrus\Installer
{
    static $installPackages = array();
}

b::begin();
b::prepare(new Package('P2'));
b::prepare(new Package('P1-1.1.0RC1'));
b::preCommitDependencyResolve();
$test->assertEquals(3, count(b::$installPackages), '3 packages should be installed');
$test->assertEquals('1.1.0RC1', b::$installPackages['pear2.php.net/P1']->version['release'], 'verify P1-1.1.0RC1');
b::rollback();
// this passes if no exceptions are thrown
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===