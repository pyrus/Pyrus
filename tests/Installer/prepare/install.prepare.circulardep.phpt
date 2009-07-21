--TEST--
\pear2\Pyrus\Installer::prepare(), complex deep circular dependency might result in 2 versions requested for same package
--FILE--
<?php
/**
 * Create a dependency tree like so:
 *
 * P1 -> P2
 *
 * P2 -> P3
 *
 * P3 -> P4
 *
 * P4-1.0.0 -> P1 <= 1.2.0
 * P4-1.1.0 -> P1
 *
 * and P1 1.3.0 exists
 */
use pear2\Pyrus\Package;
define('MYDIR', __DIR__);
include __DIR__ . '/../setup.php.inc';
require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/install.prepare.circulardep',
                       'http://pear2.php.net/');
\pear2\Pyrus\Main::$downloadClass = 'Internet';
class b extends \pear2\Pyrus\Installer
{
    static $installPackages = array();
}

b::begin();
b::prepare(new Package('P1'));
b::prepare(new Package('P4-1.0.0'));
b::preCommitDependencyResolve();
$test->assertEquals(4, count(b::$installPackages), '4 packages should be installed');
$test->assertEquals('1.2.0', b::$installPackages['pear2.php.net/P1']->version['release'], 'verify P1-1.2.0');
$names = array_keys(b::$installPackages);
sort($names);
$test->assertEquals(array('pear2.php.net/P1',
                          'pear2.php.net/P2',
                          'pear2.php.net/P3',
                          'pear2.php.net/P4'), $names, 'package names');
b::rollback();
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