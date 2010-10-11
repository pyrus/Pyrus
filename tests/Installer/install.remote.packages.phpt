--TEST--
\PEAR2\Pyrus\Installer: install remote packages with complex dependencies
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
 */include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/installer.prepare.dep.versionconflict',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';
\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare(new \PEAR2\Pyrus\Package('pear2/P1-1.0.0'));
\PEAR2\Pyrus\Installer::prepare(new \PEAR2\Pyrus\Package('pear2/P4-stable', true));
\PEAR2\Pyrus\Installer::commit();
$reg = \PEAR2\Pyrus\Config::current()->registry;
for ($i = 1; $i <= 4; $i++) {
    $test->assertTrue(isset($reg->package["P$i"]), "installed P$i");
}
$test->assertFalse(isset($reg->package['P5']), 'installed P5');
$test->assertEquals('1.0.0', $reg->info('P1', 'pear2.php.net', 'version'), 'P1 version');
$test->assertEquals('1.2.2', $reg->info('P2', 'pear2.php.net', 'version'), 'P2 version');
$test->assertEquals('1.0.0', $reg->info('P3', 'pear2.php.net', 'version'), 'P3 version');
$test->assertEquals('1.0.0', $reg->info('P4', 'pear2.php.net', 'version'), 'P4 version');

for ($i = 1; $i <= 4; $i++) {
    $test->assertFileExists(TESTDIR . "/php/glooby$i", "P$i file not installed");
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===