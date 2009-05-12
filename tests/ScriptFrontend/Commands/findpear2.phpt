--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::_findPEAR test 2: no userfile detected
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.php.inc';
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
touch(__DIR__ . '/testfoo');

test_scriptfrontend::$stdin = array(
    'yes', // answer to "It appears you have not used Pyrus before, welcome!  Initialize install?"
    __DIR__ . '/testfoo', // answer to "Where would you like to install packages by default?"
    __DIR__ . '/testit2', // answer to "Where would you like to install packages by default?"
    'no',  // answer to "Create it?"
    __DIR__ . '/testit', // answer to "Where would you like to install packages by default?"
    'yes', // answer to "Create it?"
);
$cli = new test_scriptfrontend();

ob_start();
$cli->run($args = array ());
$contents = ob_get_contents();
ob_end_clean();

$help1 = "Pyrus: No user configuration file detected\n" .
"It appears you have not used Pyrus before, welcome!  Initialize install?\n" .
"Please choose:\n" .
"  yes\n" .
"  no\n" .
"[yes] : Great.  We will store your configuration in:\n" .
'  ' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR . "foo.xml\n" .
"Where would you like to install packages by default?\n" .
'[' . getcwd() . "] : You have chosen:\n" .
__DIR__ . DIRECTORY_SEPARATOR . "testfoo\n" .
__DIR__ . DIRECTORY_SEPARATOR . "testfoo exists, and is not a directory\n" .
"Where would you like to install packages by default?\n" .
'[' . getcwd() . "] : You have chosen:\n" .
__DIR__ . DIRECTORY_SEPARATOR . "testit2\n" .
" this path does not yet exist\n" .
"Create it?\n" .
"Please choose:\n" .
"  yes\n" .
"  no\n" .
"[yes] : Where would you like to install packages by default?\n" .
'[' . getcwd() . "] : You have chosen:\n" .
__DIR__ . DIRECTORY_SEPARATOR . "testit\n" .
" this path does not yet exist\n" .
"Create it?\n" .
"Please choose:\n" .
"  yes\n" .
"  no\n" .
"[yes] : Thank you, enjoy using Pyrus\n" .
"Documentation is at http://pear.php.net\n" .
'Using PEAR installation found at ' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . "\n" 
;
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
'config-show [PEARPath]' . "\n" .
'set [PEARPath]' . "\n" .
'mypear [PEARPath]' . "\n";
$test->assertEquals($help1 . $help2,
                    $contents,
                    'initialize choice');
?>
===DONE===
--CLEAN--
<?php
unlink(__DIR__ . '/testfoo');
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===