--TEST--
\Pyrus\ScriptFrontend\Commands::upgradeRegistry()
--INI--
error_reporting = 0;
--SKIPIF--
<?php
if (!($f = @fopen('PEAR.php', 'r', true))) {
    die('SKIP - PEAR is required');
}
fclose($f);
?>
--FILE--
<?php
require __DIR__ . '/setup.minimal.php.inc';

include __DIR__ . '/setup.pearinstall.php.inc';

$test->assertEquals(array('Pear1'), \Pyrus\Registry::detectRegistries(TESTDIR),
                    'after install, verify Pear1 registry exists');

// now for the Pyrus portion of this test
set_include_path(TESTDIR);

$a = \Pyrus\Config::singleton(TESTDIR, str_replace('/', DIRECTORY_SEPARATOR, TESTDIR . '/plugins/pearconfig.xml'));
$a->ext_dir = TESTDIR . DIRECTORY_SEPARATOR . 'ext';
$a->bin_dir = TESTDIR . DIRECTORY_SEPARATOR . 'bin';
mkdir(TESTDIR . DIRECTORY_SEPARATOR . 'plugins');
file_put_contents(TESTDIR . '/plugins/pearconfig.xml', '<pearconfig version="1.0"></pearconfig>');
restore_include_path();

ob_start();
$cli = new test_scriptfrontend();
$cli->run($args = array (0 => 'upgrade-registry', TESTDIR));

$contents = ob_get_contents();
ob_end_clean();
$help1 = 'Using PEAR installation found at ' . TESTDIR . "\n";
$d = DIRECTORY_SEPARATOR;
$help2 = "Upgrading registry at path " . TESTDIR . "\n";
   

$test->assertEquals($help1 . $help2,
                    $contents,
                    'upgrade-registries output');


$test->assertEquals(array('Sqlite3', 'Xml', 'Pear1'), \Pyrus\Registry::detectRegistries(TESTDIR),
                    'verify registry upgrade');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
