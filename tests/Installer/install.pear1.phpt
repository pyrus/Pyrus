--TEST--
\PEAR2\Pyrus\Installer: install into Pear1 registry
--FILE--
<?php
include __DIR__ . '/../test_framework.php.inc';
$package = new \PEAR2\Pyrus\Package(__DIR__.'/../Mocks/SimpleChannelServer/package.xml');
@mkdir(TESTDIR);
@mkdir(TESTDIR . '/php');
@mkdir(TESTDIR . '/php/.registry');

$c = getTestConfig();
\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
\PEAR2\Pyrus\Installer::commit();

$test->assertEquals(true, file_exists(TESTDIR . '/bin/pearscs'), 'script was installed');
$test->assertFileExists(TESTDIR . '/php/.registry', 'Pear1 registry exists');
$test->assertFileExists(TESTDIR . '/php/.filemap', 'Pear1 filemap exists');
$test->assertFileExists(TESTDIR . '/php/.depdb', 'Pear1 depdb exists');
$test->assertFileExists(TESTDIR . '/php/.depdblock', 'Pear1 depdblock exists');
$test->assertFileExists(TESTDIR . '/php/.channels', 'Pear1 channel registry exists');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===