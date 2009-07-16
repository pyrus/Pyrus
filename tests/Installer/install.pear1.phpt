--TEST--
\pear2\Pyrus\Installer: install into Pear1 registry
--FILE--
<?php
include dirname(__FILE__) . '/../test_framework.php.inc';
$package = new \pear2\Pyrus\Package(__DIR__.'/../Mocks/SimpleChannelServer/package.xml');
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit/php');
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit/php/.registry');

$c = \pear2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();
\pear2\Pyrus\Installer::begin();
\pear2\Pyrus\Installer::prepare($package);
\pear2\Pyrus\Installer::commit();

$test->assertEquals(true, file_exists(__DIR__ . '/testit/bin/pearscs'), 'script was installed');
$test->assertFileExists(__DIR__ . '/testit/php/.registry', 'Pear1 registry exists');
$test->assertFileExists(__DIR__ . '/testit/php/.filemap', 'Pear1 filemap exists');
$test->assertFileExists(__DIR__ . '/testit/php/.depdb', 'Pear1 depdb exists');
$test->assertFileExists(__DIR__ . '/testit/php/.depdblock', 'Pear1 depdblock exists');
$test->assertFileExists(__DIR__ . '/testit/php/.channels', 'Pear1 channel registry exists');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===