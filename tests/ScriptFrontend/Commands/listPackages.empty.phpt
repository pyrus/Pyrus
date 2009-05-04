--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::listPackages(), no packages installed
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
ob_start();
mkdir(__DIR__ . '/testit');
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands();
$cli->run($args = array (__DIR__ . '/testit', 'list-packages'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . 'Listing installed packages [' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . ']:' . "\n",
                    $contents,
                    'list packages');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===