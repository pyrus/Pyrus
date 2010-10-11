--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::set()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
set_include_path(TESTDIR);
$a = \PEAR2\Pyrus\Config::singleton(TESTDIR, TESTDIR . '/plugins/pearconfig.xml');
$a->ext_dir = TESTDIR . '/ext';
$a->bin_dir = TESTDIR . '/bin';
file_put_contents(TESTDIR . '/plugins/pearconfig.xml', '<pearconfig version="1.0"></pearconfig>');

ob_start();
$cli = new test_scriptfrontend();
$cli->run($args = array (0 => 'set', 'ext_dir', 'poo'));

$contents = ob_get_contents();
ob_end_clean();
$help1 = 'Using PEAR installation found at ' . TESTDIR . "\n";
$d = DIRECTORY_SEPARATOR;
$help2 = "Setting ext_dir in system paths\n";
   

$test->assertEquals($help1 . $help2,
                    $contents,
                    'set output');
$test->assertEquals('poo', \PEAR2\Pyrus\Config::current()->ext_dir, 'confirm value changed');
$a = simplexml_load_file(TESTDIR . '/.config');
$test->assertEquals('poo', (string)$a->ext_dir, 'confirm value saved');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===