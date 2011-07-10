--TEST--
\Pyrus\Installer::install() verify files installed correctly
--FILE--
<?php
include __DIR__ . '/../test_framework.php.inc';
$package = new \Pyrus\Package(__DIR__.'/../Mocks/SimpleChannelServer/package.xml');
$c = getTestConfig();
\Pyrus\Installer::begin();
\Pyrus\Installer::prepare($package);
\Pyrus\Installer::commit();
$test->assertFileExists(TESTDIR . '/bin/pearscs', 'bin/pearscs');

// chmod is not fully supported on windows
if (substr(PHP_OS, 0, 3) != 'WIN') {
	$test->assertEquals(decoct(0755), decoct(0777 & fileperms(TESTDIR . '/bin/pearscs')), 'bin/pearscs perms');
}

$test->assertFileExists(TESTDIR . '/php/PEAR2/SimpleChannelServer.php', 'src/PEAR2/SimpleChannelServer.php');
$test->assertEquals(file_get_contents(__DIR__.'/../Mocks/SimpleChannelServer/src/SimpleChannelServer.php'),
                    file_get_contents(TESTDIR . '/php/PEAR2/SimpleChannelServer.php'), 'files match');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===