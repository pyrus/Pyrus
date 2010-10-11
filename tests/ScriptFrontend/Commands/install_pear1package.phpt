--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::install(), basic test
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

set_include_path(TESTDIR);
$c = \PEAR2\Pyrus\Config::singleton(TESTDIR, TESTDIR . '/plugins/pearconfig.xml');
restore_include_path();

ob_start();
$cli = new \PEAR2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'install', __DIR__ . '/packages/Net_URL-1.0.15.tar'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n"
                    . 'Downloading pear.php.net/Net_URL
Installed pear.php.net/Net_URL-1.0.15' . "\n",
                    $contents,
                    'list packages');
                    
$test->assertFileExists(TESTDIR . '/docs/Net_URL/Net/docs/example.php', 'Docs installed to old doc directory.');


?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===