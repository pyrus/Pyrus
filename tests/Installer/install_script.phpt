--TEST--
PEAR2_Pyrus_Installer::commit() scripts should be executable
--FILE--
<?php
include dirname(__FILE__) . '/../test_framework.php.inc';
$package = new PEAR2_Pyrus_Package(__DIR__.'/../../../sandbox/SimpleChannelServer/package.xml');
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();
PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare($package);
PEAR2_Pyrus_Installer::commit();
$test->assertEquals(true, file_exists(__DIR__ . '/testit/bin/pearscs'), 'script was installed');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===