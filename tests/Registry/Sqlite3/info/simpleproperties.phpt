--TEST--
Sqlite3 PackageFile v2: test basic package.xml properties
--FILE--
<?php
require __DIR__ . '/../../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();
require __DIR__ . '/../../../PackageFile_v2/setupFiles/setupPackageFile.php.inc';
$reg = new PEAR2_Pyrus_Registry_Sqlite3(__DIR__.'/testit');
$reg->install($info);
$reg = $reg->toPackageFile($package->name, $package->channel);

require __DIR__ . '/../../AllRegistries/info/basic.template';

// don't try this at home!
$reg->fromArray(array('package' => array()));
$test->assertEquals(false, $reg->{'api-version'}, 'api-version blank');
$test->assertEquals(false, $reg->{'api-state'}, 'api-state blank');
$test->assertEquals(false, $reg->{'release-version'}, 'api-version blank');
$test->assertEquals(false, $reg->state, 'state blank');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===