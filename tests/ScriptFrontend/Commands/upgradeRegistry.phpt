--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::upgradeRegistry()
--SKIPIF--
<?php
if (!($f = @fopen('PEAR.php', 'r', true))) {
    die('SKIP - PEAR is required');
}
fclose($f);
?>
--FILE--
<?php
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}

define('MYDIR', __DIR__);
require __DIR__ . '/setup.minimal.php.inc';

include __DIR__ . '/setup.pearinstall.php.inc';

$test->assertEquals(array('Pear1'), \PEAR2\Pyrus\Registry::detectRegistries(__DIR__ . DIRECTORY_SEPARATOR . 'testit'),
                    'after install, verify Pear1 registry exists');

// now for the Pyrus portion of this test
set_include_path(dirname(__FILE__).'/testit');

$a = \PEAR2\Pyrus\Config::singleton(__DIR__ . DIRECTORY_SEPARATOR . 'testit', __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/testit/plugins/pearconfig.xml'));
$a->ext_dir = __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR . 'ext';
$a->bin_dir = __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR . 'bin';
mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR . 'plugins');
file_put_contents(__DIR__ . '/testit/plugins/pearconfig.xml', '<pearconfig version="1.0"></pearconfig>');
restore_include_path();

ob_start();
$cli = new test_scriptfrontend();
$cli->run($args = array (0 => 'upgrade-registry', __DIR__ . DIRECTORY_SEPARATOR . 'testit'));

$contents = ob_get_contents();
ob_end_clean();
$help1 = 'Using PEAR installation found at ' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . "\n";
$d = DIRECTORY_SEPARATOR;
$help2 = "Upgrading registry at path " . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . "\n";
   

$test->assertEquals($help1 . $help2,
                    $contents,
                    'upgrade-registries output');


$test->assertEquals(array('Sqlite3', 'Xml', 'Pear1'), \PEAR2\Pyrus\Registry::detectRegistries(__DIR__ . DIRECTORY_SEPARATOR . 'testit'),
                    'verify registry upgrade');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
