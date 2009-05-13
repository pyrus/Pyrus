--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::_findPEAR test 1: explicit config path
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
mkdir(__DIR__ . '/testit');
ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands();
$cli->run($args = array (__DIR__ . '/testit', 'help'));

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
'upgrade-registry [PEARPath]' . "\n" .
'run-scripts [PEARPath]' . "\n" .
'config-show [PEARPath]' . "\n" .
'set [PEARPath]' . "\n" .
'mypear [PEARPath]' . "\n";
$test->assertEquals($help1 . $help2,
                    $contents,
                    'help output');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===