--TEST--
\PEAR2\Pyrus\Installer::commit() scripts should be executable
--FILE--
<?php
include __DIR__ . '/../test_framework.php.inc';
$package = new \PEAR2\Pyrus\Package(__DIR__.'/../Mocks/SimpleChannelServer/package.xml');
@mkdir(TESTDIR);
set_include_path(TESTDIR);
$c = \PEAR2\Pyrus\Config::singleton(TESTDIR, TESTDIR . '/plugins/pearconfig.xml');
$c->bin_dir = TESTDIR . '/bin';
restore_include_path();
$c->saveConfig();
\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
\PEAR2\Pyrus\Installer::commit();
$test->assertEquals(true, file_exists(TESTDIR . '/bin/pearscs'), 'script was installed');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===