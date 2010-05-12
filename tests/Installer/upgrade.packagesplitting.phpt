--TEST--
\PEAR2\Pyrus\Installer: split a package into smaller packages on upgrade
--FILE--
<?php
/**
 * Create a parent package and split it up on upgrade
 *
 * This is to test the new ugprade mechanism in Pyrus that obsoletes the need for
 * a special subpackage dependency
 */


define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/upgrade.packagesplitting',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';
\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare(new \PEAR2\Pyrus\Package('pear2/P1-1.0.0'));
\PEAR2\Pyrus\Installer::commit();

$test->assertTrue(isset(\PEAR2\Pyrus\Config::current()->registry->package['pear2.php.net/P1']), 'installed P1-1.0.0');
$test->assertEquals('1.0.0', \PEAR2\Pyrus\Config::current()->registry->info('P1', 'pear2.php.net', 'version'),
                    'version before');
$test->assertFalse(isset(\PEAR2\Pyrus\Config::current()->registry->package['pear2.php.net/P2']), 'before installed P2');
$test->assertFalse(isset(\PEAR2\Pyrus\Config::current()->registry->package['pear2.php.net/P3']), 'before installed P3');
$test->assertFalse(isset(\PEAR2\Pyrus\Config::current()->registry->package['pear2.php.net/P4']), 'before installed P4');
$test->assertFalse(isset(\PEAR2\Pyrus\Config::current()->registry->package['pear2.php.net/P5']), 'before installed P5');

\PEAR2\Pyrus\Main::$options['upgrade'] = true;
\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare(new \PEAR2\Pyrus\Package('P1'));
\PEAR2\Pyrus\Installer::commit();

$test->assertTrue(isset(\PEAR2\Pyrus\Config::current()->registry->package['pear2.php.net/P1']), 'installed P1-1.1.0');
$test->assertEquals('1.1.0', \PEAR2\Pyrus\Config::current()->registry->info('P1', 'pear2.php.net', 'version'),
                    'version after');
$test->assertTrue(isset(\PEAR2\Pyrus\Config::current()->registry->package['pear2.php.net/P2']), 'after installed P2');
$test->assertTrue(isset(\PEAR2\Pyrus\Config::current()->registry->package['pear2.php.net/P3']), 'after installed P3');
$test->assertTrue(isset(\PEAR2\Pyrus\Config::current()->registry->package['pear2.php.net/P4']), 'after installed P4');
$test->assertTrue(isset(\PEAR2\Pyrus\Config::current()->registry->package['pear2.php.net/P5']), 'after installed P5');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===