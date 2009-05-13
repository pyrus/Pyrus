--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::set()
--FILE--
<?php
set_include_path(dirname(__FILE__).'/testit');
define('MYDIR', __DIR__);
require dirname(dirname(__FILE__)) . '/setup.php.inc';

$a = PEAR2_Pyrus_Config::singleton(__DIR__ . '/testit', __DIR__ . '/testit/foo.xml');
$a->ext_dir = __DIR__ . '/testit/ext';
$a->bin_dir = __DIR__ . '/testit/bin';
file_put_contents(__DIR__ . '/testit/foo.xml', '<pearconfig version="1.0"></pearconfig>');

ob_start();
$cli = new test_scriptfrontend();
$cli->run($args = array (0 => 'set', 'ext_dir', 'poo'));

$contents = ob_get_contents();
ob_end_clean();
$help1 = 'Using PEAR installation found at ' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . "\n";
$d = DIRECTORY_SEPARATOR;
$help2 = "Setting ext_dir in system paths\n";
   

$test->assertEquals($help1 . $help2,
                    $contents,
                    'set output');
$test->assertEquals('poo', PEAR2_Pyrus_Config::current()->ext_dir, 'confirm value changed');
$a = simplexml_load_file(__DIR__ . '/testit/.config');
$test->assertEquals('poo', (string)$a->ext_dir, 'confirm value saved');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===