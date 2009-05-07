--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::help()
--FILE--
<?php
set_include_path(dirname(__FILE__).'/testit');
require dirname(dirname(__FILE__)) . '/setup.php.inc';
ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands();
$cli->run($args = array (0 => 'help'));

$contents = ob_get_contents();
ob_end_clean();
$help1 = 'Using PEAR installation found at ' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . "\n";
$help2 =
'Commands supported:' . "\n" .
'help [PEARPath]' . "\n" .
'install [PEARPath]' . "\n" .
'uninstall [PEARPath]' . "\n" .
'download [PEARPath]' . "\n" .
'upgrade [PEARPath]' . "\n" .
'list-packages [PEARPath]' . "\n" .
'list-channels [PEARPath]' . "\n" .
'channel-discover [PEARPath]' . "\n" .
'channel-add [PEARPath]' . "\n" .
'channel-del [PEARPath]' . "\n" .
'config-show [PEARPath]' . "\n" .
'set [PEARPath]' . "\n" .
'mypear [PEARPath]' . "\n";
$test->assertEquals($help1 . $help2,
                    $contents,
                    'help output');

ob_start();
$cli->run($args = array ());

$contents = ob_get_contents();
ob_end_clean();

$test->assertEquals($help1 . $help2, $contents, 'no args help');
ob_start();
$cli->run($args = array ('fooburp'));

$contents = ob_get_contents();
ob_end_clean();

$test->assertEquals($help1 .
                    'Unknown command: fooburp' . "\n" . $help2, $contents, 'unknown command help');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===