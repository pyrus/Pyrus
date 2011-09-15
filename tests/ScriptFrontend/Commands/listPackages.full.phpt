--TEST--
\Pyrus\ScriptFrontend\Commands::listPackages(), packages installed
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$c = getTestConfig();

ob_start();
$cli = new \Pyrus\ScriptFrontend\Commands(true);
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
$cli = new \Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'list-packages'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . '
Listing installed packages [' . TESTDIR . ']:
[channel pyrus.net]:
(no packages installed in channel pyrus.net)
[channel pecl.php.net]:
(no packages installed in channel pecl.php.net)
[channel pear.php.net]:
(no packages installed in channel pear.php.net)
[channel doc.php.net]:
(no packages installed in channel doc.php.net)
[channel __uri]:
(no packages installed in channel __uri)
[channel pear2.php.net]:
PEAR2_SimpleChannelServer 0.1.0 devel
',
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