--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::listPackages(), packages installed
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$c = getTestConfig();

ob_start();
$cli = new \PEAR2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'install', __DIR__.'/../../Mocks/SimpleChannelServer/package.xml'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n"
                    . 'Downloading pear2.php.net/PEAR2_SimpleChannelServer
Installed pear2.php.net/PEAR2_SimpleChannelServer-0.1.0' . "\n",
                    $contents,
                    'list packages');

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
$cli->run($args = array (TESTDIR, 'list-packages'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n"
                    . 'Listing installed packages [' . TESTDIR . ']:' . "\n"
                    . "[channel pear2.php.net]:\n"
                    . " PEAR2_SimpleChannelServer\n",
                    $contents,
                    'list packages');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===