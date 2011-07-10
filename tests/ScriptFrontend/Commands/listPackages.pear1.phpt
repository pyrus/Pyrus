--TEST--
\Pyrus\ScriptFrontend\Commands::listPackages(), existing PEAR1 registry, packages installed
--FILE--
<?php
require __DIR__ . '/setup.minimal.php.inc';
set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1');
$c = \Pyrus\Config::singleton(__DIR__.'/listPackages.pear1', TESTDIR . '/plugins/pearconfig.xml');
restore_include_path();

$cli = new \Pyrus\ScriptFrontend\Commands(true);

ob_start();
set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1');
$cli->run($args = array (__DIR__.'/listPackages.pear1', 'list-packages'));
restore_include_path();

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'listPackages.pear1' . "\n"
                    . 'Listing installed packages [' . __DIR__ . DIRECTORY_SEPARATOR . 'listPackages.pear1]:' . "\n"
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