--TEST--
\pear2\Pyrus\ScriptFrontend\Commands::install(), basic test
--FILE--
<?php
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
require __DIR__ . '/setup.php.inc';
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = \pear2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();

ob_start();
$cli = new \pear2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (__DIR__ . '/testit', 'install', __DIR__.'/../../Mocks/SimpleChannelServer/package.xml'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . 'Downloading pear2.php.net/PEAR2_SimpleChannelServer
Installed pear2.php.net/PEAR2_SimpleChannelServer-0.1.0' . "\n",
                    $contents,
                    'list packages');

$test->assertFileExists(__DIR__ . '/testit/bin/pearscs', 'bin/pearscs');
$test->assertEquals(decoct(0755), decoct(0777 & fileperms(__DIR__ . '/testit/bin/pearscs')), 'bin/pearscs perms');
$test->assertFileExists(__DIR__ . '/testit/php/PEAR2/SimpleChannelServer.php', 'php/PEAR2/SimpleChannelServer.php');
$test->assertEquals(file_get_contents(__DIR__.'/../../Mocks/SimpleChannelServer/src/SimpleChannelServer.php'),
                    file_get_contents(__DIR__ . '/testit/php/PEAR2/SimpleChannelServer.php'), 'files match');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===