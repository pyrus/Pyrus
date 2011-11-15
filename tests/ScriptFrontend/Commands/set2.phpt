--TEST--
\Pyrus\ScriptFrontend\Commands::set() on specific channel
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
set_include_path(TESTDIR);
$a = \Pyrus\Config::singleton(TESTDIR, TESTDIR . '/plugins/pearconfig.xml');
$a->ext_dir = TESTDIR . '/ext';
$a->bin_dir = TESTDIR . '/bin';
file_put_contents(TESTDIR . '/plugins/pearconfig.xml', '<pearconfig version="1.0"></pearconfig>');

ob_start();
$cli = new test_scriptfrontend();
$cli->run($args = array (0 => 'set', '-c', 'pecl.php.net', 'handle', 'poo'));

$contents = ob_get_contents();
ob_end_clean();
$help1 = 'Using PEAR installation found at ' . TESTDIR . "\n";
$d = DIRECTORY_SEPARATOR;
$help2 = "Setting handle for channel pecl.php.net in " . TESTDIR . "/plugins/foo.xml\n";

$test->assertEquals($help1 . $help2,
                    $contents,
                    'set output');
\Pyrus\Config::current()->default_channel = 'pecl.php.net';
$test->assertEquals('poo', \Pyrus\Config::current()->handle, 'confirm value changed');
$a = simplexml_load_file(TESTDIR . '/plugins/foo.xml');
$test->assertEquals('poo', (string)$a->handle[0]->peclDOTphpDOTnet, 'confirm value saved');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
