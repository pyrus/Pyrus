--TEST--
\Pyrus\Installer::prepare(), duplicate remote package, different version
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
use Pyrus\Package;
include __DIR__ . '/../setup.php.inc';
require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/install.prepare.circulardep',
                       'http://pear2.php.net/');
\Pyrus\Main::$downloadClass = 'Internet';
class b extends \Pyrus\Installer
{
    static $installPackages = array();
}

b::begin();
b::prepare(new Package('P4'));
b::prepare(new Package('P4'));
b::prepare(new Package('P4-1.1.0'));
$test->assertEquals(1, count(b::$installPackages), 'after 1.1.0');
$test->assertEquals('1.1.0', b::$installPackages['pear2.php.net/P4']->version['release'], 'version');
b::prepare(new Package('P4'));
$test->assertEquals(1, count(b::$installPackages), 'after 1.1.0');
$test->assertEquals('1.1.0', b::$installPackages['pear2.php.net/P4']->version['release'], 'version');
try {
    b::prepare(new Package('P4-1.0.0'));
    throw new Exception('should fail and did not');
} catch (Pyrus\Installer\Exception $e) {
    $test->assertEquals('Cannot install pear2.php.net/P4, two conflicting' .
            ' versions were requested (1.0.0 and 1.1.0)', $e->getMessage(), 'error');
}
b::rollback();
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===