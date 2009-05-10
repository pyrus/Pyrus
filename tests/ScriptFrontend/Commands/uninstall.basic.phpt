--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::uninstall(), basic test
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$package = new PEAR2_Pyrus_Package(__DIR__.'/../../../../sandbox/SimpleChannelServer/package.xml');
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
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
$test->assertEquals(file_get_contents(__DIR__.'/../../../../sandbox/SimpleChannelServer/src/SimpleChannelServer.php'),
                    file_get_contents(__DIR__ . '/testit/src/PEAR2/SimpleChannelServer.php'), 'files match');

ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands();
$cli->run($args = array (__DIR__ . '/testit', 'uninstall', 'pear2/PEAR2_SimpleChannelServer', 'pear/foobar'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . 'Package pear/foobar not installed, cannot uninstall' . "\n"
                    . 'Uninstalled pear2.php.net/PEAR2_SimpleChannelServer' . "\n",
                    $contents,
                    'list packages');

$test->assertFileNotExists(__DIR__ . '/testit/bin/pearscs', 'bin/pearscs after');
$test->assertFileNotExists(__DIR__ . '/testit/src/PEAR2/SimpleChannelServer.php', 'src/PEAR2/SimpleChannelServer.php after');
$test->assertEquals(false, isset(PEAR2_Pyrus_Config::current()->registry->package['pear2.php.net/PEAR2_SimpleChannelServer']), 'verify uninstalled');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===