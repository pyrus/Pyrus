--TEST--
\Pyrus\Installer: split a package into smaller packages on upgrade
--FILE--
<?php
/**
 * Create a parent package and split it up on upgrade
 *
 * This is to test the new ugprade mechanism in Pyrus that obsoletes the need for
 * a special subpackage dependency
 */
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/upgrade.packagesplitting',
                       'http://pear2.php.net/');
\Pyrus\Main::$downloadClass = 'Internet';
\Pyrus\Installer::begin();
\Pyrus\Installer::prepare(new \Pyrus\Package('pear2/P1-1.0.0'));
\Pyrus\Installer::commit();

$test->assertTrue(isset(\Pyrus\Config::current()->registry->package['pear2.php.net/P1']), 'installed P1-1.0.0');
$test->assertEquals('1.0.0', \Pyrus\Config::current()->registry->info('P1', 'pear2.php.net', 'version'),
                    'version before');
$test->assertFalse(isset(\Pyrus\Config::current()->registry->package['pear2.php.net/P2']), 'before installed P2');
$test->assertFalse(isset(\Pyrus\Config::current()->registry->package['pear2.php.net/P3']), 'before installed P3');
$test->assertFalse(isset(\Pyrus\Config::current()->registry->package['pear2.php.net/P4']), 'before installed P4');
$test->assertFalse(isset(\Pyrus\Config::current()->registry->package['pear2.php.net/P5']), 'before installed P5');

\Pyrus\Main::$options['upgrade'] = true;
\Pyrus\Installer::begin();
\Pyrus\Installer::prepare(new \Pyrus\Package('P1'));
\Pyrus\Installer::commit();

$test->assertTrue(isset(\Pyrus\Config::current()->registry->package['pear2.php.net/P1']), 'installed P1-1.1.0');
$test->assertEquals('1.1.0', \Pyrus\Config::current()->registry->info('P1', 'pear2.php.net', 'version'),
                    'version after');
$test->assertTrue(isset(\Pyrus\Config::current()->registry->package['pear2.php.net/P2']), 'after installed P2');
$test->assertTrue(isset(\Pyrus\Config::current()->registry->package['pear2.php.net/P3']), 'after installed P3');
$test->assertTrue(isset(\Pyrus\Config::current()->registry->package['pear2.php.net/P4']), 'after installed P4');
$test->assertTrue(isset(\Pyrus\Config::current()->registry->package['pear2.php.net/P5']), 'after installed P5');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===