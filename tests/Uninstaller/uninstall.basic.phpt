--TEST--
\PEAR2\Pyrus\UnInstaller::uninstall() basic test
--FILE--
<?php
include __DIR__ . '/../test_framework.php.inc';
$package = new \PEAR2\Pyrus\Package(__DIR__.'/../Mocks/SimpleChannelServer/package.xml');
$c = getTestConfig();
\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
\PEAR2\Pyrus\Installer::commit();
$test->assertFileExists(TESTDIR . '/bin/pearscs', 'bin/pearscs');

// chmod is not fully supported on windows
if (substr(PHP_OS, 0, 3) != 'WIN') {
	$test->assertEquals(decoct(0755), decoct(0777 & fileperms(TESTDIR . '/bin/pearscs')), 'bin/pearscs perms');
}

$test->assertFileExists(TESTDIR . '/php/PEAR2/SimpleChannelServer.php', 'php/PEAR2/SimpleChannelServer.php');
$test->assertEquals(file_get_contents(__DIR__.'/../Mocks/SimpleChannelServer/src/SimpleChannelServer.php'),
                    file_get_contents(TESTDIR . '/php/PEAR2/SimpleChannelServer.php'), 'files match');

\PEAR2\Pyrus\Uninstaller::begin();
\PEAR2\Pyrus\Uninstaller::prepare('pear2.php.net/PEAR2_SimpleChannelServer');
\PEAR2\Pyrus\Uninstaller::commit();

$test->assertFileNotExists(TESTDIR . '/bin/pearscs', 'bin/pearscs after');
$test->assertFileNotExists(TESTDIR . '/php/PEAR2/SimpleChannelServer.php', 'php/PEAR2/SimpleChannelServer.php after');
$test->assertEquals(false, isset(\PEAR2\Pyrus\Config::current()->registry->package['pear2.php.net/PEAR2_SimpleChannelServer']), 'verify uninstalled');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===