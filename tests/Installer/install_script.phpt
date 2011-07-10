--TEST--
\Pyrus\Installer::commit() scripts should be executable
--FILE--
<?php
include __DIR__ . '/../test_framework.php.inc';
$package = new \Pyrus\Package(__DIR__.'/../Mocks/SimpleChannelServer/package.xml');
$c = getTestConfig();
\Pyrus\Installer::begin();
\Pyrus\Installer::prepare($package);
\Pyrus\Installer::commit();
$test->assertEquals(true, file_exists(TESTDIR . '/bin/pearscs'), 'script was installed');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===