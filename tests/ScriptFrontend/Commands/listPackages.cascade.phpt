--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::listPackages(), cascading include_path
--FILE--
<?php
require __DIR__ . '/setup.minimal.php.inc';
if (file_exists(TESTDIR)) {
    include __DIR__ . '/../../clean.php.inc';
}
@mkdir(TESTDIR);
mkdir(TESTDIR . '/php');
chdir(TESTDIR);

set_include_path(TESTDIR . DIRECTORY_SEPARATOR . 'php' . PATH_SEPARATOR . __DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1');
\PEAR2\Pyrus\Config::singleton(false, TESTDIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'pearconfig.xml');
restore_include_path();

ob_start();
$cli = new \PEAR2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array ('list-packages'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installations found at ' . TESTDIR . PATH_SEPARATOR .
                    __DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1' . "\n"
                    . 'Listing installed packages [' . TESTDIR . ']:' . "\n"
                    . 'Listing installed packages [' . __DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1' . ']:' . "\n"
                    . "[channel pear.php.net]:\n"
                    . 'Archive_Tar 1.3.3 stable
Console_Getopt 1.2.3 stable
PEAR 1.8.2 stable
PHP_Archive 0.11.4 alpha
Structures_Graph 1.0.2 stable
Text_Diff 1.1.0 stable
XML_Util 1.2.1 stable
xdebug 2.0.0 stable',
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