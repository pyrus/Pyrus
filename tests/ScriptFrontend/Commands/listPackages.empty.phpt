--TEST--
\Pyrus\ScriptFrontend\Commands::listPackages(), no packages installed
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

set_include_path(TESTDIR);
\Pyrus\Config::singleton(TESTDIR, TESTDIR . '/plugins/pearconfig.xml');
restore_include_path();
ob_start();
$cli = new \Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'list-packages'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n"
                    . 'Listing installed packages [' . TESTDIR . ']:' . "\n",
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