--TEST--
PEAR2_Pyrus_UnInstaller::rollback() test
--FILE--
<?php
include dirname(__FILE__) . '/../test_framework.php.inc';
$package = new PEAR2_Pyrus_Package(__DIR__.'/../../../sandbox/SimpleChannelServer/package.xml');
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();
PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare($package);
PEAR2_Pyrus_Installer::commit();
$test->assertFileExists(__DIR__ . '/testit/bin/pearscs', 'bin/pearscs');
$test->assertEquals(decoct(0755), decoct(0777 & fileperms(__DIR__ . '/testit/bin/pearscs')), 'bin/pearscs perms');
$test->assertFileExists(__DIR__ . '/testit/src/PEAR2/SimpleChannelServer.php', 'src/PEAR2/SimpleChannelServer.php');
$test->assertEquals(file_get_contents(__DIR__.'/../../../sandbox/SimpleChannelServer/src/SimpleChannelServer.php'),
                    file_get_contents(__DIR__ . '/testit/src/PEAR2/SimpleChannelServer.php'), 'files match');

PEAR2_Pyrus_Uninstaller::begin();
PEAR2_Pyrus_Uninstaller::prepare('pear2.php.net/PEAR2_SimpleChannelServer');
PEAR2_Pyrus_Uninstaller::rollback();

$test->assertFileExists(__DIR__ . '/testit/bin/pearscs', 'bin/pearscs after');
$test->assertFileExists(__DIR__ . '/testit/src/PEAR2/SimpleChannelServer.php', 'src/PEAR2/SimpleChannelServer.php after');
$test->assertEquals(true, isset(PEAR2_Pyrus_Config::current()->registry->package['pear2.php.net/PEAR2_SimpleChannelServer']), 'verify uninstalled');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===