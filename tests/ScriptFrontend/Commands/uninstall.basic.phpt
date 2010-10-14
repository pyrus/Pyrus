--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::uninstall(), basic test
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$package = new \PEAR2\Pyrus\Package(__DIR__.'/../../Mocks/SimpleChannelServer/package.xml');

$c = getTestConfig();
\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
\PEAR2\Pyrus\Installer::commit();
$test->assertFileExists(TESTDIR . '/bin/pearscs', 'bin/pearscs');

// chmod is not fully supported on windows
if (substr(PHP_OS, 0, 3) != 'WIN') {
    $test->assertEquals(decoct(0755), decoct(0777 & fileperms(TESTDIR . '/bin/pearscs')), 'bin/pearscs perms');
}

$test->assertFileExists(TESTDIR . '/php/PEAR2/SimpleChannelServer.php', 'src/PEAR2/SimpleChannelServer.php');
$test->assertEquals(file_get_contents(__DIR__.'/../../Mocks/SimpleChannelServer/src/SimpleChannelServer.php'),
                    file_get_contents(TESTDIR . '/php/PEAR2/SimpleChannelServer.php'), 'files match');

ob_start();
$cli = new \PEAR2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'uninstall', 'pear2/PEAR2_SimpleChannelServer', 'pear/foobar'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n"
                    . 'Package pear/foobar not installed, cannot uninstall' . "\n"
                    . 'Uninstalled pear2.php.net/PEAR2_SimpleChannelServer' . "\n",
                    $contents,
                    'list packages');

$test->assertFileNotExists(TESTDIR . '/bin/pearscs', 'bin/pearscs after');
$test->assertFileNotExists(TESTDIR . '/php/PEAR2/SimpleChannelServer.php', 'src/PEAR2/SimpleChannelServer.php after');
$test->assertEquals(false, isset(\PEAR2\Pyrus\Config::current()->registry->package['pear2.php.net/PEAR2_SimpleChannelServer']), 'verify uninstalled');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===