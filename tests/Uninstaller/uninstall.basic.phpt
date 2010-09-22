--TEST--
\PEAR2\Pyrus\UnInstaller::uninstall() basic test
--FILE--
<?php
include dirname(__FILE__) . '/../test_framework.php.inc';
$package = new \PEAR2\Pyrus\Package(__DIR__.'/../Mocks/SimpleChannelServer/package.xml');
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = \PEAR2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();
\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
\PEAR2\Pyrus\Installer::commit();
$test->assertFileExists(__DIR__ . '/testit/bin/pearscs', 'bin/pearscs');

// chmod is not fully supported on windows
if (substr(PHP_OS, 0, 3) != 'WIN') {
	$test->assertEquals(decoct(0755), decoct(0777 & fileperms(__DIR__ . '/testit/bin/pearscs')), 'bin/pearscs perms');
}

$test->assertFileExists(__DIR__ . '/testit/php/PEAR2/SimpleChannelServer.php', 'php/PEAR2/SimpleChannelServer.php');
$test->assertEquals(file_get_contents(__DIR__.'/../Mocks/SimpleChannelServer/src/SimpleChannelServer.php'),
                    file_get_contents(__DIR__ . '/testit/php/PEAR2/SimpleChannelServer.php'), 'files match');

\PEAR2\Pyrus\Uninstaller::begin();
\PEAR2\Pyrus\Uninstaller::prepare('pear2.php.net/PEAR2_SimpleChannelServer');
\PEAR2\Pyrus\Uninstaller::commit();

$test->assertFileNotExists(__DIR__ . '/testit/bin/pearscs', 'bin/pearscs after');
$test->assertFileNotExists(__DIR__ . '/testit/php/PEAR2/SimpleChannelServer.php', 'php/PEAR2/SimpleChannelServer.php after');
$test->assertEquals(false, isset(\PEAR2\Pyrus\Config::current()->registry->package['pear2.php.net/PEAR2_SimpleChannelServer']), 'verify uninstalled');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===